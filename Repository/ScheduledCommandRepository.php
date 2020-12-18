<?php

namespace Xact\CommandScheduler\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
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
            FROM Xact\CommandScheduler\Entity\ScheduledCommand c
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
                FROM Xact\CommandScheduler\Entity\ScheduledCommand c
                WHERE c.disabled = true AND COALESCE(c.cronExpression, '') = '' AND c.lastRunAt < :purgeDate"
            )->setParameter('purgeDate', $purgeDate)
            ->getResult();

            foreach ($purgeCommands as $cmd) {
                // Delete the command history records
                $this->getEntityManager()->createQuery(
                    "DELETE Xact\CommandScheduler\Entity\ScheduledCommandHistory h
                    WHERE h.scheduledCommand=:cmd"
                )->setParameter('cmd', $cmd['id'])
                ->execute();

                // And then the command
                $this->getEntityManager()->createQuery(
                    "DELETE Xact\CommandScheduler\Entity\ScheduledCommand c
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
