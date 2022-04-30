<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Entity\ScheduledCommandHistory;

/**
 * ScheduledCommand repository class
 */
class ScheduledCommandRepository extends EntityRepository
{
    protected string $commandEntity = ScheduledCommand::class;
    protected string $historyEntity = ScheduledCommandHistory::class;

    /**
     * Finds an entity by its primary key / identifier.
     *
     * @param int      $id          The identifier.
     * @param int|null $lockMode    One of the \Doctrine\DBAL\LockMode::* constants
     *                              or NULL if no specific lock mode should be used
     *                              during the search.
     * @param int|null $lockVersion The lock version.
     */
    // phpcs:ignore
    public function findById(int $id, $lockMode = null, $lockVersion = null): ?ScheduledCommand
    {
        return $this->getEntityManager()->find($this->commandEntity, $id, $lockMode, $lockVersion);
    }

    /**
     * Return an array of active scheduled commands
     *
     * @return ScheduledCommand[]
     */
    public function getActiveCommands(): array
    {
        return $this->getEntityManager()->createQuery(
            "SELECT c
            FROM {$this->commandEntity} c
            WHERE c.disabled = false
            AND (c.runImmediately = true OR COALESCE(c.cronExpression, '') != '' OR c.runAt <= CURRENT_TIMESTAMP())
            AND c.status = 'PENDING'
            ORDER BY c.priority DESC"
        )->getResult();
    }

    /**
     * Clean up old once-only commands
     */
    public function cleanUpOnceOnlyCommands(int $afterDays = 60): void
    {
        $purgeDate = new DateTime("-{$afterDays} day");
        $em = $this->getEntityManager();

        $em->beginTransaction();
        try {
            /**
             * DQL does not support DELETE with JOIN so we need to derive a list of command ids to delete
             *
             * Get a list of command ids that we need to purge
             */
            $purgeCommands = $this->getEntityManager()->createQuery(
                "SELECT c.id
                FROM {$this->commandEntity} c
                WHERE c.disabled = true AND COALESCE(c.cronExpression, '') = '' AND c.lastRunAt < :purgeDate"
            )->setParameter('purgeDate', $purgeDate)
            ->getResult();

            foreach ($purgeCommands as $cmd) {
                // Delete the command history records
                $this->getEntityManager()->createQuery(
                    "DELETE {$this->historyEntity} h
                    WHERE h.scheduledCommand=:cmd"
                )->setParameter('cmd', $cmd['id'])
                ->execute();

                // And then the command
                $this->getEntityManager()->createQuery(
                    "DELETE {$this->commandEntity} c
                    WHERE c.id=:cmd"
                )->setParameter('cmd', $cmd['id'])
                ->execute();
            }

            $em->flush();
            $em->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();
            echo "An error occurred purging old scheduled commands: {$e->getMessage()}";
        }
    }
}
