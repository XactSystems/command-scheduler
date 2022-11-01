<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\Scheduler;

use Doctrine\ORM\EntityManagerInterface;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Repository\ScheduledCommandRepository;

/**
 * Command scheduler service class
 */
class CommandScheduler
{
    private ScheduledCommandRepository $commandRepository;
    private EntityManagerInterface $em;


    public function __construct(ScheduledCommandRepository $commandRepository, EntityManagerInterface $em)
    {
        $this->commandRepository = $commandRepository;
        $this->em = $em;
    }

    /**
     * Get a scheduled command by ID
     */
    public function get(int $id): ScheduledCommand
    {
        return $this->commandRepository->findById($id);
    }

    /**
     * Update a scheduled command
     */
    public function set(ScheduledCommand $scheduledCommand): ScheduledCommand
    {
        $this->em->persist($scheduledCommand);
        $this->em->flush();

        return $scheduledCommand;
    }

    /**
     * Delete a scheduled command
     */
    public function delete(ScheduledCommand $scheduledCommand): void
    {
        $this->em->remove($scheduledCommand);
        $this->em->flush();
    }

    /**
     * Get an array of active commands
     *
     * @return ScheduledCommand[]
     */
    public function getActive(): array
    {
        return $this->commandRepository->getActiveCommands();
    }

    /**
     * Get an array of completed commands
     *
     * @return ScheduledCommand[]
     */
    public function getCompleted(): array
    {
        return $this->commandRepository->getCompletedCommands();
    }

    /**
     * Get an array of all commands
     *
     * @return ScheduledCommand[]
     */
    public function getAll(): array
    {
        return $this->commandRepository->findAll();
    }

    /**
     * Disable/Enable by ID
     */
    public function disable(int $id, bool $disable = true): ScheduledCommand
    {
        $scheduledCommand = $this->get($id);
        $scheduledCommand->setDisabled($disable);
        if (!$disable) {
            $scheduledCommand->setStatus(ScheduledCommand::STATUS_PENDING);
        }
        $this->em->flush();

        return $scheduledCommand;
    }

    /**
     * Run immediately by ID
     */
    public function runImmediately(int $id, bool $runImmediately = true): ScheduledCommand
    {
        $scheduledCommand = $this->get($id);
        $scheduledCommand->setRunImmediately($runImmediately);
        $this->em->flush();

        return $scheduledCommand;
    }
}
