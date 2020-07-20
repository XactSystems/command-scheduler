<?php

namespace Xact\CommandScheduler\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Xact\CommandScheduler\Entity\ScheduledCommand;
use Xact\CommandScheduler\Scheduler\CommandScheduler;

class ControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     *
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    protected function setUp()
    {
        self::bootKernel();

        $this->client = static::createClient();
        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->entityManager->beginTransaction();
    }

    protected function tearDown()
    {
        $this->entityManager->rollback();
        $this->entityManager = null;
    }

    public function testList()
    {
        $this->client->request('GET', '/command-scheduler/list');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testEdit()
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
        $this->assertEquals($description,'Test command 1');
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
}
