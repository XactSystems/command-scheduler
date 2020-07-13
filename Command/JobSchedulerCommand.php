<?php

namespace Xact\JobScheduler\Command;

use App\Entity\ScheduledCommand;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Enqueue\Client\ProducerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Xact\JobScheduler\Queue\RunCommandProcessor;

class JobSchedulerCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:event:scheduler';

    /**
     * @var ProducerInterface
     */
    private $enqueueProducer;

    /**
     * @var EntityManagerInterface|EntityManager
     */
    private $entityManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * EventSchedulerCommand constructor.
     *
     * @param ProducerInterface $enqueueProducer
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ProducerInterface $enqueueProducer, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct(self::$defaultName);

        $this->enqueueProducer = $enqueueProducer;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Schedules commands to be executed using Enqueue events')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        foreach ($this->entityManager->getRepository(ScheduledCommand::class)->findAll() as $command) {

            if ($command->getLastTriggeredAt()
                && $command->getLastTriggeredAt()->getTimestamp() + $command->getFrequency() > time()) {
                //command does not need to be triggered (for now)
                continue;
            }

            $this->enqueueProducer->sendCommand(RunCommandProcessor::COMMAND_NAME, [
                'id' => $command->getId(),
                'env' => null,
            ]);
            $this->logger->info("JobSchedulerCommand - Triggering {$command->getId()}");

            $command->setLastTriggeredAt(new \DateTime());
            $this->entityManager->flush();
        }

        return true;
    }

}
