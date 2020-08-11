<?php

namespace Xact\CommandScheduler\Command;

use Cron\CronExpression;
use Doctrine\ORM\EntityManagerInterface;
use Enqueue\Client\ProducerInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Scheduler\CommandSchedulerFactory;

class SchedulerCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'xact:command-scheduler';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var int
     */
    private $startTime;

    /**
     * @var int
     */
    private $maxRuntime;

    /**
     * @var int
     */
    private $idleTime;

    /**
     * @var int
     */
    private $deleteOldJobsAfter = 0;

    /**
     * @var \Symfony\Component\Console\Input\InputInterface;
     */
    private $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * EventSchedulerCommand constructor.
     *
     * @param ProducerInterface $enqueueProducer
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct(self::$defaultName);

        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Schedules commands to be executed via cron expressions')
            ->addOption('max-runtime', 'r', InputOption::VALUE_OPTIONAL, 'The maximum runtime in seconds. 0 runs forever.', 0)
            ->addOption('idle-time', null, InputOption::VALUE_OPTIONAL, 'Seconds to sleep when the command queue is empty.', 5)
            ->addOption('delete-old-jobs-after', null, InputOption::VALUE_OPTIONAL, 'Days after which to delete old single-run jobs. 0 is never.', 60)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->verbosity = $output->getVerbosity();
        $this->input = $input;
        $this->output = $output;
        $this->maxRuntime = (integer) $input->getOption('max-runtime');
        if ($this->maxRuntime < 0) {
            throw new InvalidArgumentException('The maximum runtime must be greater than or equal to zero.');
        }

        $this->idleTime = (integer) $input->getOption('idle-time');
        if ($this->idleTime <= 0) {
            throw new InvalidArgumentException('Seconds to sleep when idle must be greater than zero.');
        }
        $this->deleteOldJobsAfter = (integer) $input->getOption('delete-old-jobs-after');
        if ($this->deleteOldJobsAfter < 0) {
            throw new InvalidArgumentException('Delete old jobs must be greater than or equal to zero.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startTime = time();

        $this->runCommands();
    }

    /**
     * Run the scheduled commands
     *
     * @return void
     */
    protected function runCommands()
    {
        $this->output->writeln('Running scheduled commands.');

        while (true) {
            if ($this->exceededMaxRuntime()) {
                break;
            }

            $this->processCommands();

            $this->cleanUpOnceOnlyCommands();

            sleep($this->idleTime);
        }

        $this->output->writeln('The command scheduler has terminated.');
    }

    /**
     * Determine if the maximum runtime has been exceeded
     *
     * @return boolean
     */
    protected function exceededMaxRuntime(): bool
    {
        return (($this->maxRuntime > 0) && (time() - $this->startTime) > $this->maxRuntime);
    }

    protected function processCommands()
    {
        /** @var ScheduledCommand $command */
        foreach ($this->em->getRepository(ScheduledCommand::class)->getActiveCommands() as $command) {

            $execute = $command->getRunImmediately();
            if(!$execute && !empty($command->getCronExpression())) {
                $cron = CronExpression::factory($command->getCronExpression());
                if ($cron->getNextRunDate($command->getLastRunAt()) <= new \DateTime()) {
                    $execute = true;
                }
            }

            if($execute) {
                $this->executeCommand($command);
            }

            if ($this->exceededMaxRuntime()) {
                break;
            }
        }

        // Clear the EntityManager to avoid conflict between commands and make sure no entities are managed
        $this->em->clear();
    }

    /**
     * Run the command
     * 
     * @param ScheduledCommand $scheduledCommand
     */
    protected function executeCommand(ScheduledCommand $scheduledCommand)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $scheduledCommand->setLastRunAt(new \DateTime());
            $this->em->flush();

            $this->em->getConnection()->commit();

            $input = new StringInput(
                $scheduledCommand->getCommand() . ' ' . $scheduledCommand->getArguments() . ' --env=' . $this->input->getOption('env')
            );

            $output = new ConsoleOutput();
            // Disable interactive mode if the current command has no-interaction flag
            if (true === $input->hasParameterOption(['--no-interaction', '-n'])) {
                $input->setInteractive(false);
                $output = new NullOutput();
            }

            $command = $this->getApplication()->find($scheduledCommand->getCommand());

            // Execute the command and retain the return code
            $this->output->writeln(
                '<info>Execute</info> : <comment>' . $scheduledCommand->getCommand()
                . ' ' . $scheduledCommand->getArguments() . '</comment>'
            );
            $result = $command->run($input, $output);
            $resultText = 'The command completed successfully';
        } catch (CommandNotFoundException $e) {
            $resultText = $e->getMessage();
            $this->output->writeln("<error>{$e->getMessage()}</error>");
        } catch (\Exception $e) {
            $resultText = $e->getMessage();
            $this->output->writeln("<error>{$e->getMessage()}</error>");
            $this->output->writeln("<error>{$e->getTraceAsString()}</error>");
            $result = -1;
        } finally {
            if (false === $this->em->isOpen()) {
                $output->writeln('<comment>Entity manager closed by the last command.</comment>');
                $this->em = $this->em->create($this->em->getConnection(), $this->em->getConfiguration());
            }

            $scheduledCommand->setRunImmediately(false);
            $scheduledCommand->setLastResultCode($result);
            $scheduledCommand->setLastResult($resultText);

            CommandSchedulerFactory::createCommandHistory(($scheduledCommand));

            // Disable any once-only commands
            if (empty($scheduledCommand->getCronExpression())) {
                $scheduledCommand->setDisabled(true);
            }

            $this->em->flush();

            unset($command);
        }

        gc_collect_cycles();
    }

    /**
     * Purge old once-only commands
     *
     * @return void
     */
    protected function cleanUpOnceOnlyCommands()
    {
        if ($this->deleteOldJobsAfter > 0) {
            $this->em->getRepository(ScheduledCommand::class)->cleanUpOnceOnlyCommands($this->deleteOldJobsAfter);
        }
    }
}
