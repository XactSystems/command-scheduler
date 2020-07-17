<?php

namespace Xact\CommandScheduler\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;

/**
 * ScheduledCommand repository class
 */
class ScheduledCommandRepository extends EntityRepository
{
    /**
     * Return an array of active scheduled commands
     *
     * @return array
     */
    public function getActiveCommands():array
    {
        return $this->getEntityManager()->createQuery(
            "SELECT c
            FROM XactCommandSchedulerBundle:ScheduledCommand c
            WHERE c.disabled = false AND (c.runImmediately = true OR COALESCE(c.cronExpression, '') != '')
            ORDER BY c.priority DESC"
        )->getResult();
    }

    /**
     * Clean up old once-only commands
     *
     * @return void
     */
    public function cleanUpOnceOnlyCommands()
    {
        $purgeDate = new DateTime('-2 month');
        $this->getEntityManager()->createQuery(
            "DELETE c
            FROM XactCommandSchedulerBundle:ScheduledCommand c
            WHERE c.disabled = true AND COALESCE(c.cronExpression, '') = '' AND c.lastRunAt < :purgeDate"
        )->setParameter('purgeDate', $purgeDate)
        ->execute();
    }
}

