<?php

namespace Xact\CommandScheduler\Scheduler;

use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Entity\ScheduledCommandHistory;

/**
 * Command scheduler factory class
 */
class CommandSchedulerFactory
{
    /**
     * Create a ScheduledCommandHistory entity from a ScheduledCommand
     *
     * @param \Xact\CommandScheduler\Entity\ScheduledCommand $scheduledCommand
     * @return \Xact\CommandScheduler\Entity\ScheduledCommandHistory
     */
    public static function createCommandHistory(ScheduledCommand $scheduledCommand): ScheduledCommandHistory
    {
        $history = new ScheduledCommandHistory();
        $history->setScheduledCommand($scheduledCommand);
        $history->setResultCode($scheduledCommand->getLastResultCode());
        $history->setResult($scheduledCommand->getLastResult());
        $history->setRunAt($scheduledCommand->getLastRunAt());
        $scheduledCommand->getCommandHistory()->add($history);

        return $history;
    }
}
