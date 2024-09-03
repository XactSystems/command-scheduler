<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\Repository;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Entity\ScheduledCommandHistory;

/**
 * ScheduledCommand repository class
 *
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\Xact\CommandScheduler\Entity\ScheduledCommand>
 */
class ScheduledCommandRepository extends ServiceEntityRepository
{
    protected string $historyEntity = ScheduledCommandHistory::class;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduledCommand::class);
    }

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
        return $this->getEntityManager()->find($this->getClassName(), $id, $lockMode, $lockVersion);
    }

    /**
     * Return an array of active scheduled commands
     *
     * @return ScheduledCommand[]
     */
    public function getActiveCommands(): array
    {
        /** @var ScheduledCommand[] */
        return $this->getEntityManager()->createQuery(
            "SELECT c
            FROM {$this->getClassName()} c
            WHERE c.disabled = false
            AND (c.runImmediately = true OR COALESCE(c.cronExpression, '') != '' OR c.runAt <= CURRENT_TIMESTAMP() OR c.retryAt <= CURRENT_TIMESTAMP())
            AND c.status = 'PENDING'
            ORDER BY c.priority DESC"
        )->getResult();
    }

    /**
     * Return an array of completed scheduled commands
     *
     * @return ScheduledCommand[]
     */
    public function getCompletedCommands(): array
    {
        /** @var ScheduledCommand[] */
        return $this->getEntityManager()->createQuery(
            "SELECT c
            FROM {$this->getClassName()} c
            WHERE c.disabled = true
            ORDER BY c.priority DESC, c.lastRunAt DESC"
        )->getResult();
    }

    /**
     * Clean up old once-only commands
     */
    public function cleanUpOnceOnlyCommands(int $afterDays = 60, int $idleTime = 5): void
    {
        $startTime = time();
        $purgeDate = new DateTime("-{$afterDays} day");
        $em = $this->getEntityManager();

        $em->beginTransaction();
        try {
            /**
             * Get a list of command ids that we need to purge.
             * DQL does not support DELETE with JOIN so we need to derive a list of command ids to delete
             */
            while (true) {
                /** @var int[] */
                $commandIds = $this->getEntityManager()->createQuery(
                    "SELECT c.id
                    FROM {$this->getClassName()} c
                    WHERE c.disabled = true AND COALESCE(c.cronExpression, '') = '' AND c.lastRunAt < :purgeDate"
                )   ->setParameter('purgeDate', $purgeDate)
                    ->setMaxResults(50)
                    ->getResult();
                if (count($commandIds) === 0) {
                    break;
                }

                $this->getEntityManager()->createQuery(
                    "DELETE {$this->historyEntity} h
                    WHERE h.scheduledCommand IN(:idList)"
                )   ->setParameter('idList', $commandIds)
                    ->execute();

                // And then the command
                $this->getEntityManager()->createQuery(
                    "DELETE {$this->getClassName()} c
                    WHERE c.id IN(:idList)"
                )->setParameter('idList', $commandIds)
                ->execute();

                // Do not process deletes for more than the idle time
                if ((time() - $startTime) >= $idleTime) {
                    break;
                }
            }

            $em->flush();
            $em->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();
            echo "An error occurred purging old scheduled commands: {$e->getMessage()}";
        }
    }
}
