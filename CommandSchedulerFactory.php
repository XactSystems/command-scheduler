<?php

declare(strict_types=1);

namespace Xact\CommandScheduler;

use Cron\CronExpression;
use InvalidArgumentException;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Entity\ScheduledCommandHistory;

class CommandSchedulerFactory
{
    /**
     * Create a command to run immediately
     *
     * @param string[]|null $arguments
     */
    public static function createImmediateCommand(string $description, string $command, ?array $arguments): ScheduledCommand
    {
        $scheduledCommand = new ScheduledCommand();
        $scheduledCommand->setDescription($description);
        $scheduledCommand->setCommand($command);
        $scheduledCommand->setArguments($arguments);
        $scheduledCommand->setRunImmediately(true);

        return $scheduledCommand;
    }

    /**
     * Create a command scheduled by a CRON expression
     *
     * @param string[]|null $arguments
     */
    public static function createCronCommand(string $cronExpression, string $description, string $command, ?array $arguments): ScheduledCommand
    {
        if (!CronExpression::isValidExpression($cronExpression)) {
            throw new InvalidArgumentException("The cron expression '{$cronExpression}' is invalid");
        }

        $scheduledCommand = new ScheduledCommand();
        $scheduledCommand->setCronExpression($cronExpression);
        $scheduledCommand->setDescription($description);
        $scheduledCommand->setCommand($command);
        $scheduledCommand->setArguments($arguments);

        return $scheduledCommand;
    }

    /**
     * Create a ScheduledCommandHistory entity from a ScheduledCommand
     */
    public static function createCommandHistory(ScheduledCommand $scheduledCommand): ScheduledCommandHistory
    {
        $history = new ScheduledCommandHistory();
        $history->setScheduledCommand($scheduledCommand);
        $history->setResultCode($scheduledCommand->getLastResultCode());
        $history->setResult($scheduledCommand->getLastResult());
        $history->setError($scheduledCommand->getLastError());
        $history->setRunAt($scheduledCommand->getLastRunAt());
        $scheduledCommand->getCommandHistory()->add($history);

        return $history;
    }
}
