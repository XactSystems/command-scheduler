<?php

namespace Xact\JobScheduler\Command;

use App\Entity\ScheduledCommand;
use App\Queue\Processor\RunCommandProcessor;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Enqueue\Client\ProducerInterface;
use Mtdowling\Supervisor\EventListener;
use Mtdowling\Supervisor\EventNotification;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * EventSchedulerCommand constructor.
     *
     * @param ProducerInterface $enqueueProducer
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ProducerInterface $enqueueProducer, EntityManagerInterface $entityManager)
    {
        parent::__construct(self::$defaultName);

        $this->enqueueProducer = $enqueueProducer;
        $this->entityManager = $entityManager;
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

        $listener = new EventListener();
        $listener->listen(function (EventListener $listener, EventNotification $event) {

            $env = $this->getContainer()->getParameter('kernel.environment');
            foreach ($this->entityManager->getRepository(ScheduledCommand::class)->findAll() as $command) {

                if ($command->getLastTriggeredAt()
                    && $command->getLastTriggeredAt()->getTimestamp() + $command->getFrequency() > time()) {
                    //command does not need to be triggered (for now)
                    continue;
                }

                $this->enqueueProducer->sendCommand(RunCommandProcessor::COMMAND_NAME, [
                    'id' => $command->getId(),
                    'env' => $env,
                ]);
                $listener->log('triggering ' . $command->getId() . ' ' . ((new \DateTime())->format('Y-m-d H:i:s')));

                $command->setLastTriggeredAt(new \DateTime());
                $this->entityManager->flush();
            }

            return true;
        });

    }

}
