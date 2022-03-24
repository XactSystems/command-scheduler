<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\Scheduler;

use DateTime;
use Symfony\Component\Process\Process;
use Xact\CommandScheduler\Entity\ScheduledCommand;

class ActiveCommand
{
    protected Process $process;
    protected ScheduledCommand $scheduledCommand;
    protected \DateTime $startTime;


    public function __construct(Process $process, ScheduledCommand $scheduledCommand)
    {
        $this->process = $process;
        $this->scheduledCommand = $scheduledCommand;
        $this->startTime = new DateTime();
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    public function getScheduledCommand(): ScheduledCommand
    {
        return $this->scheduledCommand;
    }

    public function getStartTime(): DateTime
    {
        return $this->startTime;
    }
}
