<?php

declare(strict_types=1);

namespace Xact\CommandScheduler;

use Cron\CronExpression;
use InvalidArgumentException;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Entity\ScheduledCommandHistory;

class CommandSchedulerFactory
{
    protected bool $clearData = true;
    protected bool $retryOnFail = true;
    protected int $retryDelay;
    protected int $retryMaxAttempts;

    public function __construct(bool $clearData, bool $retryOnFail, int $retryDelay, int $retryMaxAttempts)
    {
        $this->clearData = $clearData;
        $this->retryOnFail = $retryOnFail;
        $this->retryDelay = $retryDelay;
        $this->retryMaxAttempts = $retryMaxAttempts;
    }

    /**
     * Create a command to run immediately
     *
     * @param string[]|null $arguments
     * @param bool|null $clearData If null, the configuration value 'clear_data' is used. Default true.
     * @param bool|null $retryOnFail If null, the configuration value 'retry_on_fail' is used. Default false.
     * @param int|null $retryDelay If null, the configuration value 'retry_delay' is used. Default 60.
     * @param int|null $retryMaxAttempts If null, the configuration value 'retry_max_attempts' is used. Default 60.
     */
    public function createImmediateCommand(
        string $description,
        string $command,
        ?array $arguments = null,
        ?string $data = null,
        ?bool $clearData = null,
        ?bool $retryOnFail = null,
        ?int $retryDelay = null,
        ?int $retryMaxAttempts = null
    ): ScheduledCommand {
        $scheduledCommand = new ScheduledCommand();
        $scheduledCommand->setDescription($description);
        $scheduledCommand->setCommand($command);
        $scheduledCommand->setArguments($arguments);
        $scheduledCommand->setRunImmediately(true);
        $scheduledCommand->setData($data);
        $scheduledCommand->setClearData($clearData ?? $this->clearData);
        $scheduledCommand->setRetryOnFail($retryOnFail ?? $this->retryOnFail);
        $scheduledCommand->setRetryDelay($retryDelay ?? $this->retryDelay);
        $scheduledCommand->setRetryMaxAttempts($retryMaxAttempts ?? $this->retryMaxAttempts);

        return $scheduledCommand;
    }

    /**
     * Create a command scheduled by a CRON expression
     *
     * @param string[]|null $arguments
     * @param bool|null $clearData If null, the configuration value 'clear_data' is used. Default true.
     * @param bool|null $retryOnFail If null, the configuration value 'retry_on_fail' is used. Default false.
     * @param int|null $retryDelay If null, the configuration value 'retry_delay' is used. Default 60.
     * @param int|null $retryMaxAttempts If null, the configuration value 'retry_max_attempts' is used. Default 60.
     */
    public function createCronCommand(
        string $cronExpression,
        string $description,
        string $command,
        ?array $arguments = null,
        ?string $data = null,
        ?bool $clearData = null,
        ?bool $retryOnFail = null,
        ?int $retryDelay = null,
        ?int $retryMaxAttempts = null
    ): ScheduledCommand {
        if (!CronExpression::isValidExpression($cronExpression)) {
            throw new InvalidArgumentException("The cron expression '{$cronExpression}' is invalid");
        }

        $scheduledCommand = new ScheduledCommand();
        $scheduledCommand->setCronExpression($cronExpression);
        $scheduledCommand->setDescription($description);
        $scheduledCommand->setCommand($command);
        $scheduledCommand->setArguments($arguments);
        $scheduledCommand->setData($data);
        $scheduledCommand->setClearData($clearData ?? $this->clearData);
        $scheduledCommand->setRetryOnFail($retryOnFail ?? $this->retryOnFail);
        $scheduledCommand->setRetryDelay($retryDelay ?? $this->retryDelay);
        $scheduledCommand->setRetryMaxAttempts($retryMaxAttempts ?? $this->retryMaxAttempts);

        return $scheduledCommand;
    }

    /**
     * Create a ScheduledCommandHistory entity from a ScheduledCommand
     */
    public function createCommandHistory(ScheduledCommand $scheduledCommand): ScheduledCommandHistory
    {
        $history = new ScheduledCommandHistory();
        $history->setScheduledCommand($scheduledCommand);
        $history->setLastResultCode($scheduledCommand->getLastResultCode());
        $history->setLastResult($scheduledCommand->getLastResult());
        $history->setLastError($scheduledCommand->getLastError());
        $history->setLastRunAt($scheduledCommand->getLastRunAt());
        $scheduledCommand->getCommandHistory()->add($history);

        return $history;
    }
}
