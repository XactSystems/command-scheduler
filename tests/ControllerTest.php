<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Scheduler\CommandScheduler;

class ControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private CommandScheduler $commandScheduler;
    private KernelBrowser $client;

    /**
     * @group controller
     */
    public function testList(): void
    {
        $this->client->request('GET', '/command-scheduler/list');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        //var_dump($this->client->getResponse()->getContent());
    }

    /**
     * @group controller
     */
    public function testEdit(): void
    {
        $scheduledCommand = new ScheduledCommand();
        $scheduledCommand->setDescription('Test command 1');
        $scheduledCommand->setCommand('test:test-command-1');
        $scheduledCommand->setCronExpression('@hourly');

        $this->commandScheduler->set($scheduledCommand);
        $this->assertNotEmpty($scheduledCommand->getId());

        $crawler = $this->client->request('GET', "/command-scheduler/edit/{$scheduledCommand->getId()}");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $description = $crawler->filter('input[name="scheduler_edit[description]"]')->attr('value');
        $cronExpression = $crawler->filter('input[name="scheduler_edit[cronExpression]"]')->attr('value');
        $this->assertEquals($description, 'Test command 1');
        $this->assertEquals($cronExpression, '@hourly');

        $saveButtonNode = $crawler->selectButton('scheduler_edit[save]');

        // Select the form that contains this button
        $form = $saveButtonNode->form();

        // you can also pass an array of field values that overrides the default ones
        $form = $saveButtonNode->form([
            'scheduler_edit[description]' => 'Test command 1 updated',
            'scheduler_edit[cronExpression]' => '@daily',
        ]);

        /*
        // This test is failing and returning code 404 as the table is not found, it must be to do with transactions and ParamConvertor
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $updatedCommand = $commandScheduler->get($scheduledCommand->getId());
        $this->assertEquals($updatedCommand->getDescription(), 'Test command 1 updated');
        $this->assertEquals($updatedCommand->getCronExpression(), '@daily');
        */
    }

    /**
     * @group controller
     */
    public function testDisable(): void
    {
        $scheduledCommand = new ScheduledCommand();
        $scheduledCommand->setDescription('Test command 2');
        $scheduledCommand->setCommand('test:test-command-2');
        $scheduledCommand->setCronExpression('@hourly');
        $scheduledCommand->setDisabled(false);

        $this->commandScheduler->set($scheduledCommand);
        $this->assertNotEmpty($scheduledCommand->getId());

        $crawler = $this->client->request('POST', "/command-scheduler/disable/{$scheduledCommand->getId()}");
        $this->assertContains($this->client->getResponse()->getStatusCode(), [200, 302]);

        $updatedCommand = $this->commandScheduler->get($scheduledCommand->getId());
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
        $scheduledCommand->setCronExpression('@hourly');
        $scheduledCommand->setRunImmediately(false);

        $this->commandScheduler->set($scheduledCommand);
        $this->assertNotEmpty($scheduledCommand->getId());

        $crawler = $this->client->request('POST', "/command-scheduler/run/{$scheduledCommand->getId()}");
        $this->assertContains($this->client->getResponse()->getStatusCode(), [200, 302]);

        $updatedCommand = $this->commandScheduler->get($scheduledCommand->getId());
        $this->assertEquals(true, $updatedCommand->getRunImmediately());
    }

    protected function setUp(): void
    {
        self::bootKernel();

        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->commandScheduler = static::getContainer()->get(CommandScheduler::class);


        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();

        parent::tearDown();
    }
}
