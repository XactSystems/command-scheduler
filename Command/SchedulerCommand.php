<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\Command;

use Cron\CronExpression;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
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
    protected static string $commandName = 'xact:command-scheduler';

    private CommandSchedulerFactory $factory;
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

    public function __construct(CommandSchedulerFactory $factory, EntityManagerInterface $em, ScheduledCommandRepository $commandRepository, LoggerInterface $logger)
    {
        parent::__construct(self::$commandName);

        $this->factory = $factory;
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

        $this->configureDebugOutput($output);
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

        return 0;
    }

    /**
     * Run the scheduled commands
     */
    protected function runCommands(): void
    {
        if ($this->verbosity !== OutputInterface::VERBOSITY_QUIET) {
            $this->writeLine('Running scheduled commands.');
        }

        while (true) {
            if ($this->isMaxRuntimeExceeded()) {
                $this->writeDebugLine("SchedulerCommand::runCommands quitting process loop.");
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
            $this->writeLine('The command scheduler has terminated.');
        }
    }

    /**
     * Determine if the maximum runtime has been exceeded
     */
    protected function isMaxRuntimeExceeded(): bool
    {
        $currentTime = time();
        $this->writeDebugLine("SchedulerCommand::isMaxRuntimeExceeded maxRuntime:{$this->maxRuntime}, startTime:{$this->startTime}, time:{$currentTime}.");
        return (($this->maxRuntime > 0) && ($currentTime - $this->startTime) > $this->maxRuntime);
    }

    protected function processCommands(): void
    {
        $tNow = new \DateTime();
        $commands = $this->commandRepository->getActiveCommands();
        $commandCount = count($commands);
        $this->writeDebugLine("SchedulerCommand::processCommands found {$commandCount} commands to process.");
        foreach ($commands as $command) {
            try {
                $execute = $command->getRunImmediately();
                if (!$execute && !empty($command->getCronExpression())) {
                    $cron = CronExpression::factory($command->getCronExpression());
                    $lastRun = $command->getLastRunAt() ?? new \DateTime('1970-01-01');
                    if ($cron->getNextRunDate($lastRun) <= $tNow) {
                        $execute = true;
                    }
                }

                if (
                    $execute
                    || ($command->getRunAt() !== null && $command->getRunAt() <= $tNow)
                    || ($command->getRetryAt() !== null && $command->getRetryAt() <= $tNow)
                ) {
                    $this->executeCommand($command);
                }

                if ($this->isMaxRuntimeExceeded()) {
                    break;
                }
            } catch (\Exception $e) {
                $this->writeLine(
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
            if ($scheduledCommand->getArguments() !== null) {
                foreach ($scheduledCommand->getArguments() as $param) {
                    $commandArguments[] = $param;
                }
            }

            $process = new Process($commandArguments);
            $process->start();

            $this->activeCommands[] = new ActiveCommand($process, $scheduledCommand);

            if ($this->verbosity !== OutputInterface::VERBOSITY_QUIET) {
                $args = $scheduledCommand->getArguments() ?? [];
                $description = $scheduledCommand->getDescription() ?? $scheduledCommand->getCommand();
                $executeMessage = '<info>Execute</info> : <comment>' . $description
                    . ($this->verbosity > OutputInterface::VERBOSITY_NORMAL ? implode(',', $args) : '')
                    . '</comment>';
                $this->writeLine($executeMessage);
            }
        } catch (\Exception $e) {
            $this->writeLine("<error>{$e->getMessage()}</error>");
            $this->logger->critical($e->getMessage());
            $this->logger->critical($e->getTraceAsString());
        }

        gc_collect_cycles();
    }

    /**
     * Check for terminated active commands, update them, and remove them from the list
     */
    protected function checkActiveCommands(): void
    {
        $activeCount = count($this->activeCommands);
        $this->writeDebugLine("SchedulerCommand::checkActiveCommands found {$activeCount} active commands.");
        foreach ($this->activeCommands as $index => $ac) {
            if ($ac->getProcess()->isRunning()) {
                // Keep the output and error updated if verbose
                if ($this->verbosity > OutputInterface::VERBOSITY_NORMAL) {
                    $description = $ac->getScheduledCommand()->getDescription() ?? $ac->getScheduledCommand()->getCommand();
                    $output = $ac->getProcess()->getIncrementalOutput();
                    $error = $ac->getProcess()->getIncrementalErrorOutput();

                    if (!empty($output)) {
                        $this->writeLine("{$description}:" . str_replace("\n", "\n{$description}:", $output));
                    }

                    if (!empty($error)) {
                        $this->writeLine("<error>{$description}:" . str_replace("\n", "\n{$description}:", $error) . '</error>');
                    }
                }

                continue;
            }

            $process = $ac->getProcess();
            $scheduledCommand = $this->commandRepository->findById($ac->getScheduledCommand()->getId());

            if ($scheduledCommand === null) {
                $this->writeDebugLine(
                    "<error>SchedulerCommand::checkActiveCommands ScheduledCommand entity not found for ID {$ac->getScheduledCommand()->getId()} when terminate.</error>"
                );
            } else {
                $this->writeDebugLine("SchedulerCommand::checkActiveCommands Command ID {$scheduledCommand->getId()} has terminated with code {$process->getExitCode()}.");
            }

            if (null !== $scheduledCommand) {
                try {
                    $this->em->getConnection()->beginTransaction();

                    if ($this->verbosity != OutputInterface::VERBOSITY_QUIET) {
                        $description = $scheduledCommand->getDescription() ?? $scheduledCommand->getCommand();
                        $this->writeLine($description . ' completed with exit code ' . $ac->getProcess()->getExitCode() . '.');
                    }

                    $resultTest = $process->getOutput();
                    if (empty($process->getExitCode()) && empty($resultTest)) {
                        $resultTest = 'The command completed successfully.';
                    }
                    $scheduledCommand->setRunImmediately(false);
                    $scheduledCommand->setLastResultCode($process->getExitCode());
                    $scheduledCommand->setLastResult($resultTest);
                    $scheduledCommand->setLastError($process->getErrorOutput());

                    $this->factory->createCommandHistory($scheduledCommand);

                    // Reschedule failed commands if retry is enabled
                    if ($scheduledCommand->getLastResultCode() !== 0 && $scheduledCommand->getRetryOnFail()) {
                        if ($scheduledCommand->getRetryCount() >= $scheduledCommand->getRetryMaxAttempts()) {
                            $scheduledCommand->setRetryAt(null);
                            if (empty($scheduledCommand->getCronExpression())) {
                                $scheduledCommand->setDisabled(true);
                                $scheduledCommand->setStatus(ScheduledCommand::STATUS_RETRIES_EXCEEDED);
                            } else {
                                $scheduledCommand->setStatus(ScheduledCommand::STATUS_PENDING);
                            }
                            $this->writeLine(
                                "<error>Scheduled command {$scheduledCommand->getId()}, '{$scheduledCommand->getDescription()}', " .
                                "has failed and exceeded the maximum number of retries.</error>"
                            );
                            if ($scheduledCommand->getClearData()) {
                                $scheduledCommand->setData(null);
                            }
                        } else {
                            $scheduledCommand->setRetryCount($scheduledCommand->getRetryCount() + 1);
                            $scheduledCommand->setStatus(ScheduledCommand::STATUS_PENDING);
                            $retryAt = new DateTime("+ {$scheduledCommand->getRetryDelay()} second");
                            $scheduledCommand->setRetryAt($retryAt);
                            $this->writeLine(
                                "<comment>Scheduled command {$scheduledCommand->getId()}, '{$scheduledCommand->getDescription()}', has failed and has been rescheduled.</comment>"
                            );
                        }
                    } else {
                        // Disable any once-only commands
                        if (empty($scheduledCommand->getCronExpression())) {
                            $scheduledCommand->setDisabled(true);
                            $scheduledCommand->setStatus(ScheduledCommand::STATUS_COMPLETED);
                            if ($scheduledCommand->getClearData()) {
                                $scheduledCommand->setData(null);
                            }
                        } else {
                            $scheduledCommand->setStatus(ScheduledCommand::STATUS_PENDING);
                        }
                        $scheduledCommand->setRetryAt(null);
                    }


                    // Initialise on-success and on-failure commands
                    if (empty($process->getExitCode()) && $scheduledCommand->getOnSuccessCommand() !== null) {
                        $successCommand = $scheduledCommand->getOnSuccessCommand();
                        $successCommand->setRunImmediately(true);
                        $successCommand->setOriginalCommand($scheduledCommand);
                    }
                    if (!empty($process->getExitCode()) && $scheduledCommand->getOnFailureCommand() !== null) {
                        $failureCommand = $scheduledCommand->getOnFailureCommand();
                        $failureCommand->setRunImmediately(true);
                        $failureCommand->setOriginalCommand($scheduledCommand);
                    }

                    $this->em->flush();
                    $this->em->getConnection()->commit();
                } catch (\Exception $e) {
                    $this->writeLine("<error>{$e->getMessage()}</error>");
                    $this->logger->critical($e->getMessage());
                    $this->logger->critical($e->getTraceAsString());
                }
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
            $this->writeDebugLine("SchedulerCommand::cleanUpOnceOnlyCommands cleaning deleteOldJobsAfter(days):{$this->deleteOldJobsAfter}.");
            $this->commandRepository->cleanUpOnceOnlyCommands($this->deleteOldJobsAfter, $this->idleTime);
        }
    }

    protected function writeLine(string $message): void
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $this->output->writeln("{$now} {$message}");
    }

    protected function writeDebugLine(string $message): void
    {
        if ($this->verbosity === OutputInterface::VERBOSITY_DEBUG) {
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $this->output->writeln("<debug>{$now} {$message}</debug>");
        }
    }

    protected function configureDebugOutput(OutputInterface $output): void
    {
        $debugStyle = new OutputFormatterStyle('magenta', 'black', ['bold']);
        $output->getFormatter()->setStyle('debug', $debugStyle);
    }
}
