<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Scheduler\CommandScheduler;

class CommandSchedulerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    /**
     * @group scheduler
     */
    public function testSet(): void
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

    /**
     * @group scheduler
     */
    public function testDelete(): void
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

    /**
     * @group scheduler
     */
    public function testActive(): void
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

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }
}
