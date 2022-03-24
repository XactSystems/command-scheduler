<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\Command;

use Cron\CronExpression;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Xact\CommandScheduler\CommandSchedulerFactory;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Repository\ScheduledCommandRepository;
use Xact\CommandScheduler\Scheduler\ActiveCommand;

class SchedulerCommand extends Command
{
    protected static string $defaultName = 'xact:command-scheduler';

    private EntityManagerInterface $em;
    private int $startTime = 0;
    private int $maxRuntime = 0;
    private int $idleTime = 5;
    private int $deleteOldJobsAfter = 0;
    private int $verbosity = OutputInterface::VERBOSITY_QUIET;
    private InputInterface $input;
    private OutputInterface $output;
    private ScheduledCommandRepository $commandRepository;
    private LoggerInterface $logger;
    /** @var ActiveCommand[] */
    private array $activeCommands = [];

    public function __construct(EntityManagerInterface $em, ScheduledCommandRepository $commandRepository, LoggerInterface $logger)
    {
        parent::__construct(self::$defaultName);

        $this->em = $em;
        $this->commandRepository = $commandRepository;
        $this->logger = $logger;
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
        $this->maxRuntime = (int) $input->getOption('max-runtime');
        if ($this->maxRuntime < 0) {
            throw new InvalidArgumentException('The maximum runtime must be greater than or equal to zero.');
        }

        $this->idleTime = (int) $input->getOption('idle-time');
        if ($this->idleTime <= 0) {
            throw new InvalidArgumentException('Seconds to sleep when idle must be greater than zero.');
        }
        $this->deleteOldJobsAfter = (int) $input->getOption('delete-old-jobs-after');
        if ($this->deleteOldJobsAfter < 0) {
            throw new InvalidArgumentException('Delete old jobs must be greater than or equal to zero.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startTime = time();

        $this->runCommands();
    }

    /**
     * Run the scheduled commands
     */
    protected function runCommands(): void
    {
        if ($this->verbosity !== OutputInterface::VERBOSITY_QUIET) {
            $this->output->writeln('Running scheduled commands.');
        }

        while (true) {
            if ($this->exceededMaxRuntime()) {
                break;
            }

            $this->processCommands();

            $this->checkActiveCommands();

            $this->cleanUpOnceOnlyCommands();

            sleep($this->idleTime);
        }

        while (! empty($this->activeCommands)) {
            sleep(5);
            $this->checkActiveCommands();
        }

        if ($this->verbosity !== OutputInterface::VERBOSITY_QUIET) {
            $this->output->writeln('The command scheduler has terminated.');
        }
    }

    /**
     * Determine if the maximum runtime has been exceeded
     */
    protected function exceededMaxRuntime(): bool
    {
        return (($this->maxRuntime > 0) && (time() - $this->startTime) > $this->maxRuntime);
    }

    protected function processCommands(): void
    {
        $tNow = new \DateTime();
        foreach ($this->commandRepository->getActiveCommands() as $command) {
            try {
                $execute = $command->getRunImmediately();
                if (!$execute && !empty($command->getCronExpression())) {
                    $cron = CronExpression::factory($command->getCronExpression());
                    $lastRun = $command->getLastRunAt() ?? new \DateTime('1970-01-01');
                    if ($cron->getNextRunDate($lastRun) <= $tNow) {
                        $execute = true;
                    }
                }

                if ($execute || $command->getRunAt() <= $tNow) {
                    $this->executeCommand($command);
                }

                if ($this->exceededMaxRuntime()) {
                    break;
                }
            } catch (\Exception $e) {
                $this->output->writeln(
                    '<error>An exception has occurred scheduling the command ' .
                    $command->getCommand() . ': ' . $e->getMessage() . '</error>'
                );
            }
        }

        // Clear the EntityManager to avoid conflict between commands and make sure no entities are managed
        $this->em->clear();
    }

    /**
     * Run the command
     */
    protected function executeCommand(ScheduledCommand $scheduledCommand): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $scheduledCommand->setLastRunAt(new \DateTime());
            $scheduledCommand->setStatus(ScheduledCommand::STATUS_RUNNING);
            $this->em->flush();

            $this->em->getConnection()->commit();

            $commandArguments = $this->getFixedCommandArguments($scheduledCommand);
            $commandArguments[] = $scheduledCommand->getCommand();
            foreach ($scheduledCommand->getArguments() as $param) {
                $commandArguments[] = $param;
            }

            $process = new Process($commandArguments);
            $process->start();

            $this->activeCommands[] = new ActiveCommand($process, $scheduledCommand);

            if ($this->verbosity !== OutputInterface::VERBOSITY_QUIET) {
                $description = $scheduledCommand->getDescription() ?? $scheduledCommand->getCommand();
                $executeMessage = '<info>Execute</info> : <comment>' . $description
                    . ($this->verbosity > OutputInterface::VERBOSITY_NORMAL ? implode(',', $scheduledCommand->getArguments()) : '')
                    . '</comment>';
                $this->output->writeln($executeMessage);
            }
        } catch (\Exception $e) {
            $this->output->writeln("<error>{$e->getMessage()}</error>");
            $this->logger->critical($e->getMessage());
            $this->logger->critical($e->getTraceAsString());
        } finally {
            unset($command);
        }

        gc_collect_cycles();
    }

    /**
     * Check for terminated active commands, update them, and remove them from the list
     */
    protected function checkActiveCommands(): void
    {
        foreach ($this->activeCommands as $index => $ac) {
            if ($ac->getProcess()->isRunning()) {
                // Keep the output and error updated if verbose
                if ($this->verbosity > OutputInterface::VERBOSITY_NORMAL) {
                    $description = $ac->getScheduledCommand()->getDescription() ?? $ac->getScheduledCommand()->getCommand();
                    $output = $ac->getProcess()->getIncrementalOutput();
                    $error = $ac->getProcess()->getIncrementalErrorOutput();

                    if (!empty($output)) {
                        $this->output->writeln("{$description}:" . str_replace("\n", "\n{$description}:", $output));
                    }

                    if (!empty($error)) {
                        $this->output->writeln("<error>{$description}:" . str_replace("\n", "\n{$description}:", $error) . '</error>');
                    }
                }

                continue;
            }

            $process = $ac->getProcess();
            $scheduledCommand = $this->commandRepository->findById($ac->getScheduledCommand()->getId());

            if (null !== $scheduledCommand) {
                if ($this->verbosity != OutputInterface::VERBOSITY_QUIET) {
                    $description = $scheduledCommand->getDescription() ?? $scheduledCommand->getCommand();
                    $this->output->writeln($description . ' completed with exit code ' . $ac->getProcess()->getExitCode() . '.');
                }

                $resultTest = $process->getOutput();
                if (empty($process->getExitCode()) && empty($resultTest)) {
                    $resultTest = 'The command completed successfully.';
                }
                $scheduledCommand->setRunImmediately(false);
                $scheduledCommand->setLastResultCode($process->getExitCode());
                $scheduledCommand->setLastResult($resultTest);
                $scheduledCommand->setLastError($process->getErrorOutput());

                CommandSchedulerFactory::createCommandHistory($scheduledCommand);

                // Disable any once-only commands
                if (empty($scheduledCommand->getCronExpression())) {
                    $scheduledCommand->setDisabled(true);
                    $scheduledCommand->setStatus(ScheduledCommand::STATUS_COMPLETED);
                } else {
                    $scheduledCommand->setStatus(ScheduledCommand::STATUS_PENDING);
                }
                if ($scheduledCommand->getClearData()) {
                    $scheduledCommand->setData(null);
                }

                $this->em->flush();
            }

            unset($this->activeCommands[$index]);
        }
    }

    /**
     * Return the fixed command arguments
     *
     * @return string[]
     */
    protected function getFixedCommandArguments(ScheduledCommand $scheduledCommand): array
    {
        $args = [
            PHP_BINARY,
            $_SERVER['argv'][0],
            '--env=' . $this->input->getOption('env'),
            '--command-id=' . $scheduledCommand->getId(),
        ];

        switch ($this->verbosity) {
            case OutputInterface::VERBOSITY_QUIET:
                $args[] = '-q';
                break;
            case OutputInterface::VERBOSITY_VERBOSE:
                $args[] = '-v';
                break;
            case OutputInterface::VERBOSITY_VERY_VERBOSE:
                $args[] = '-vv';
                break;
            case OutputInterface::VERBOSITY_DEBUG:
                $args[] = '-vvv';
                break;
        }

        return $args;
    }

    /**
     * Purge old once-only commands
     */
    protected function cleanUpOnceOnlyCommands(): void
    {
        if ($this->deleteOldJobsAfter > 0) {
            $this->commandRepository->cleanUpOnceOnlyCommands($this->deleteOldJobsAfter);
        }
    }
}
