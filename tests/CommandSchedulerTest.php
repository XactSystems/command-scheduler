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
    private CommandScheduler $commandScheduler;

    /**
     * @group scheduler
     */
    public function testSet(): void
    {
        $scheduledCommand = new ScheduledCommand();
        $scheduledCommand->setDescription('Test command 1');
        $scheduledCommand->setCommand('test:test-command-1');
        $scheduledCommand->setRunImmediately(true);

        $this->commandScheduler->set($scheduledCommand);
        $this->assertNotEmpty($scheduledCommand->getId());

        $scheduledCommand->setDescription('Changed description');
        $this->commandScheduler->set($scheduledCommand);
        $retrievedCommand = $this->commandScheduler->get($scheduledCommand->getId());
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

        $this->commandScheduler->set($scheduledCommand);
        $this->assertNotEmpty($scheduledCommand->getId());

        $this->commandScheduler->delete($scheduledCommand);
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

        $initialCount = count($this->commandScheduler->getActive());

        $this->commandScheduler->set($scheduledCommand);
        $this->assertNotEmpty($scheduledCommand->getId());

        $updatedCount = count($this->commandScheduler->getActive());
        $this->assertEquals($updatedCount, $initialCount + 1);
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->commandScheduler = static::getContainer()->get(CommandScheduler::class);
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

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

        parent::tearDown();
    }
}
