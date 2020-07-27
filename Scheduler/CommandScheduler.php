<?php

namespace Xact\CommandScheduler\Scheduler;

use Doctrine\ORM\EntityManagerInterface;
use Xact\CommandScheduler\Entity\ScheduledCommand;

/**
 * Command scheduler service class
 */
class CommandScheduler
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * Class constructor.
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Get a scheduled command by ID
     *
     * @param integer $id
     * @return \Xact\CommandScheduler\Entity\ScheduledCommand
     */
    public function get(int $id): ScheduledCommand
    {
        return $this->em->find(ScheduledCommand::class, $id);
    }

    /**
     * Update a scheduled command
     *
     * @param \Xact\CommandScheduler\Entity\ScheduledCommand $scheduledCommand
     * @return \Xact\CommandScheduler\Entity\ScheduledCommand
     */
    public function set(ScheduledCommand $scheduledCommand): ScheduledCommand
    {
        $this->em->persist($scheduledCommand);
        $this->em->flush();

        return $scheduledCommand;
    }

    /**
     * Delete a scheduled command
     *
     * @param \Xact\CommandScheduler\Entity\ScheduledCommand $scheduledCommand
     * @return void
     */
    public function delete(ScheduledCommand $scheduledCommand)
    {
        $this->em->remove($scheduledCommand);
        $this->em->flush();
    }

    /**
     * Get an array of active commands
     *
     * @return array
     */
    public function getActive(): array
    {
        return $this->em->getRepository(ScheduledCommand::class)->getActiveCommands();
    }

    /**
     * Get an array of all commands
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->em->getRepository(ScheduledCommand::class)->findAll();
    }

    /**
     * Disable by ID
     *
     * @param integer $id
     * @param boolean $disable
     * 
     * @return \Xact\CommandScheduler\Entity\ScheduledCommand
     */
    public function disable(int $id, bool $disable = true): ScheduledCommand
    {
        $scheduledCommand = $this->get($id);
        $scheduledCommand->setDisabled($disable);
        $this->em->flush();

        return $scheduledCommand;
    }

    /**
     * Run immediately by ID
     *
     * @param integer $id
     * @param boolean $runImmediately
     * 
     * @return \Xact\CommandScheduler\Entity\ScheduledCommand
     */
    public function runImmediately(int $id, bool $runImmediately = true): ScheduledCommand
    {
        $scheduledCommand = $this->get($id);
        $scheduledCommand->setRunImmediately($runImmediately);
        $this->em->flush();

        return $scheduledCommand;
    }
}

