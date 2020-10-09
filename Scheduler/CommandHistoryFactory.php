<?php

namespace Xact\CommandScheduler\Scheduler;

use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Entity\ScheduledCommandHistory;

/**
 * Command scheduler factory class
 */
class CommandHistoryFactory
{
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
