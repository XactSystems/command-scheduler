<?php

namespace Xact\CommandScheduler\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Scheduler\CommandScheduler;

class CommandSchedulerTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    protected static function getKernelClass()
    {
        return TestKernel::class;
    }

    protected function setUp()
    {
        self::bootKernel();

        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->entityManager->beginTransaction();
    }

    protected function tearDown()
    {
        $this->entityManager->rollback();
        $this->entityManager = null;
    }

    public function testSet()
    {
        $scheduledCommand = new ScheduledCommand();
        $scheduledCommand->setDescription('Test command 1');
        $scheduledCommand->setCommand('test:test-command-1');
        $scheduledCommand->setRunImmediately(true);

        $commandScheduler = new CommandScheduler($this->entityManager);
        $commandScheduler->set($scheduledCommand);
        $this->assertNotEmpty($scheduledCommand->getId());

        $scheduledCommand->setDescription('Changed description');
        $commandScheduler->set($scheduledCommand);
        $retrievedCommand = $commandScheduler->get($scheduledCommand->getId());
        $this->assertEquals($retrievedCommand->getDescription(), 'Changed description');
    }

    public function testDelete()
    {
        $scheduledCommand = new ScheduledCommand();
        $scheduledCommand->setDescription('Test command 2');
        $scheduledCommand->setCommand('test:test-command-2');
        $scheduledCommand->setRunImmediately(true);

        $commandScheduler = new CommandScheduler($this->entityManager);
        $commandScheduler->set($scheduledCommand);
        $this->assertNotEmpty($scheduledCommand->getId());

        $commandScheduler->delete($scheduledCommand);
        $this->assertEmpty($scheduledCommand->getId());
    }

    public function testActive()
    {
        $scheduledCommand = new ScheduledCommand();
        $scheduledCommand->setDescription('Test command 3');
        $scheduledCommand->setCommand('test:test-command-3');
        $scheduledCommand->setCronExpression('*/5 * * * *');

        $commandScheduler = new CommandScheduler($this->entityManager);
        $initialCount = count($commandScheduler->getActive());

        $commandScheduler->set($scheduledCommand);
        $this->assertNotEmpty($scheduledCommand->getId());

        $updatedCount = count($commandScheduler->getActive());
        $this->assertEquals($updatedCount, $initialCount + 1);
    }
}
