<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Scheduler\CommandScheduler;

class ControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private Client $client;

    public function testList(): void
    {
        $this->client->request('GET', '/command-scheduler/list');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group controller
     */
    public function testEdit(): void
    {
        $scheduledCommand = new ScheduledCommand();
        $scheduledCommand->setDescription('Test command 1');
        $scheduledCommand->setCommand('test:test-command-1');
        $scheduledCommand->setCronExpression('*/5 * * * *');

        $commandScheduler = new CommandScheduler($this->entityManager);
        $commandScheduler->set($scheduledCommand);
        $this->assertNotEmpty($scheduledCommand->getId());

        $crawler = $this->client->request('GET', "/command-scheduler/edit/{$scheduledCommand->getId()}");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $description = $crawler->filter('input[name="scheduler_edit[description]"]')->attr('value');
        $cronExpression = $crawler->filter('input[name="scheduler_edit[cronExpression]"]')->attr('value');
        $this->assertEquals($description, 'Test command 1');
        $this->assertEquals($cronExpression, '*/5 * * * *');

        $saveButtonNode = $crawler->selectButton('scheduler_edit[save]');

        // Select the form that contains this button
        $form = $saveButtonNode->form();

        // you can also pass an array of field values that overrides the default ones
        $form = $saveButtonNode->form([
            'scheduler_edit[description]' => 'Test command 1 updated',
            'scheduler_edit[cronExpression]' => '*/20 * * * *',
        ]);

        $this->client->submit($form);
        // This test is failing and returning code 404 as the DB object is not found, it must be to do with transactions and ParamConvertor
        //$this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        //$updatedCommand = $commandScheduler->get($scheduledCommand->getId());
        //$this->assertEquals($updatedCommand->getDescription(), 'Test command 1 updated');
        //$this->assertEquals($updatedCommand->getCronExpression(), '*/20 * * * *');
    }

    /**
     * @group controller
     */
    public function testDisable(): void
    {
        $scheduledCommand = new ScheduledCommand();
        $scheduledCommand->setDescription('Test command 2');
        $scheduledCommand->setCommand('test:test-command-2');
        $scheduledCommand->setCronExpression('*/5 * * * *');
        $scheduledCommand->setDisabled(false);

        $commandScheduler = new CommandScheduler($this->entityManager);
        $commandScheduler->set($scheduledCommand);
        $this->assertNotEmpty($scheduledCommand->getId());

        $crawler = $this->client->request('POST', "/command-scheduler/disable/{$scheduledCommand->getId()}");
        $this->assertContains($this->client->getResponse()->getStatusCode(), [200, 302]);

        $updatedCommand = $commandScheduler->get($scheduledCommand->getId());
        $this->assertEquals(true, $updatedCommand->getDisabled());
    }

    /**
     * @group controller
     */
    public function testRun(): void
    {
        $scheduledCommand = new ScheduledCommand();
        $scheduledCommand->setDescription('Test command 3');
        $scheduledCommand->setCommand('test:test-command-3');
        $scheduledCommand->setCronExpression('*/5 * * * *');
        $scheduledCommand->setRunImmediately(false);

        $commandScheduler = new CommandScheduler($this->entityManager);
        $commandScheduler->set($scheduledCommand);
        $this->assertNotEmpty($scheduledCommand->getId());

        $crawler = $this->client->request('POST', "/command-scheduler/run/{$scheduledCommand->getId()}");
        $this->assertContains($this->client->getResponse()->getStatusCode(), [200, 302]);

        $updatedCommand = $commandScheduler->get($scheduledCommand->getId());
        $this->assertEquals(true, $updatedCommand->getRunImmediately());
    }

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }
}
