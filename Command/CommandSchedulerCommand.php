<?php

namespace Xact\JobScheduler\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Enqueue\Client\ProducerInterface;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Xact\JobScheduler\Entity\ScheduledCommand;

class CommandSchedulerCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'xact:command-scheduler';

    /**
     * @var ProducerInterface
     */
    private $enqueueProducer;

    /**
     * @var EntityManagerInterface|EntityManager
     */
    private $entityManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $startTime;

    /**
     * @var string
     */
    private $verbosity;

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
    public function __construct(ProducerInterface $enqueueProducer, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct(self::$defaultName);

        $this->enqueueProducer = $enqueueProducer;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Schedules commands to be executed via Enqueue events')
            ->addOption('max-runtime', 'r', InputOption::VALUE_OPTIONAL, 'The maximum runtime in seconds.', 0)
            ->addOption('idle-time', null, InputOption::VALUE_OPTIONAL, 'Seconds to sleep when the command queue is empty.', 5)
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
        if ($this->maxRuntime <= 0) {
            throw new InvalidArgumentException('The maximum runtime must be greater than zero.');
        }

        $this->idleTime = (integer) $input->getOption('idle-time');
        if ($this->idleTime <= 0) {
            throw new InvalidArgumentException('Seconds to sleep when idle must be greater than zero.');
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
        if ($this->verbosity) {
            $this->output->writeln('Running scheduled commands.');
        }

        while (true) {
            if ($this->exceededMaxRuntime()) {
                break;
            }

            $this->processCommands();

            usleep(rand(1000, 5000));
        }

        if ($this->verbosity) {
            $this->output->writeln('The command scheduler is shutting down, waiting for current commands to terminate...');
        }

        if ($this->verbosity) {
            $this->output->writeln('The command scheduler has terminated.');
        }
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

    protected function checkRunningCommands()
    {

    }

    protected function processCommands()
    {
        /** @var ScheduledCommand $command */
        foreach ($this->entityManager->getRepository(ScheduledCommand::class)->findAll() as $command) {

            if ($command->getLastTriggeredAt()
                && $command->getLastTriggeredAt()->getTimestamp() + $command->getFrequency() > time()) {
                // The command does not need to be triggered yet
                continue;
            }

            $this->executeCommand($command);

            if ($this->exceededMaxRuntime()) {
                break;
            }
        }
    }

    protected function executeCommand(ScheduledCommand $scheduledCommand)
    {
        try {
            $scheduledCommand->setLastRunAt(new \DateTime());
            $this->em->flush();
            $this->em->getConnection()->commit();

            $input = new StringInput(
                $scheduledCommand->getCommand().' '.$scheduledCommand->getArguments().' --env='.$this->input->getOption('env')
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
                '<info>Execute</info> : <comment>'.$scheduledCommand->getCommand()
                .' '.$scheduledCommand->getArguments().'</comment>'
            );
            $result = $command->run($input, $output);
        } catch (\Exception $e) {
            $this->output->writeln("<error>{$e->getMessage()}</error>");
            $this->output->writeln("<error>{$e->getTraceAsString()}</error>");
            $result = -1;
        } finally {
            if (false === $this->em->isOpen()) {
                $output->writeln('<comment>Entity manager closed by the last command.</comment>');
                $this->em = $this->em->create($this->em->getConnection(), $this->em->getConfiguration());
            }

            $scheduledCommand->setLastResult($result);
            $this->em->flush();

            /*
            * Clear the EntityManager to avoid conflict between commands and make sure no entities are managed
            */
            $this->em->clear();

            unset($command);
        }

        gc_collect_cycles();
    }
}
