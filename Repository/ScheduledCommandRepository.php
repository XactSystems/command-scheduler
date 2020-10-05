<?php

namespace Xact\CommandScheduler\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Xact\CommandScheduler\Entity\ScheduledCommand;

/**
 * ScheduledCommand repository class
 */
class ScheduledCommandRepository extends EntityRepository
{
    /**
     * Return an array of active scheduled commands
     *
     * @return ScheduledCommand[]
     */
    public function getActiveCommands(): array
    {
        return $this->getEntityManager()->createQuery(
            "SELECT c
            FROM XactCommandSchedulerBundle:ScheduledCommand c
            WHERE c.disabled = false AND (c.runImmediately = true OR COALESCE(c.cronExpression, '') != '') AND c.status = 'PENDING'
            ORDER BY c.priority DESC"
        )->getResult();
    }

    /**
     * Clean up old once-only commands
     */
    public function cleanUpOnceOnlyCommands(int $afterDays = 60): void
    {
        $purgeDate = new DateTime("-{$afterDays} day");

        // Purge jobs without a cron expression
        $this->getEntityManager()->createQuery(
            "DELETE
            FROM XactCommandSchedulerBundle:ScheduledCommand c
            WHERE c.disabled = true AND COALESCE(c.cronExpression, '') = '' AND c.lastRunAt < :purgeDate"
        )->setParameter('purgeDate', $purgeDate)
        ->execute();
    }
}

