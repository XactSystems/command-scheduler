<?php


namespace Xact\JobScheduler\Queue;

use App\Entity\ScheduledCommand;
use Doctrine\ORM\EntityManagerInterface;
use Enqueue\Client\CommandSubscriberInterface;
use Enqueue\Consumption\Result;
use Enqueue\Util\JSON;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class RunCommandProcessor
 *
 * @see https://github.com/php-enqueue/enqueue-dev/issues/213
 */
class RunCommandProcessor implements Processor, CommandSubscriberInterface
{
    const COMMAND_NAME = 'run_command';

    /** @var string */
    private $projectDir;

    /** @var string */
    private $env;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * RunCommandProcessor constructor.
     * @param string $rootDir
     * @param string $env
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(string $rootDir, string $env, EntityManagerInterface $entityManager)
    {
        $this->projectDir = $rootDir;
        $this->env = $env;
        $this->entityManager = $entityManager;
    }

    /**
     * @param PsrMessage $message
     * @param PsrContext $context
     * @return Result|object|string
     */
    public function process(Message $message, Context $context)
    {
        $body = JSON::decode($message->getBody());

        $command = $this->entityManager->getRepository(ScheduledCommand::class)->findOneById($body['id']);

        if (!$command) {
            return Result::reject(sprintf('Command with ID: "%s" has not been found in the DB!', $body['id']));
        }

        $options = '';
        if ($command->getOptions()) {
            foreach ($command->getOptions() as $k => $v) {
                //env cannot be passed here
                if ('env' === $k) {
                    continue;
                }
                $options = ' --'.$k.'='.$v.' ';
            }
        }

        $command->setLastResult('<started> '.((new \DateTime())->format('Y-m-d H:i:s')));
        $this->entityManager->flush();

        $process = new Process('php ./bin/console '.$command->getCommand().' '.$options.' --env='.($body['env'] ?? $this->env), $this->projectDir);
        try {
            $process->mustRun();

            $command->setLastResult('<finished> '.$process->getOutput().PHP_EOL.PHP_EOL.$process->getErrorOutput());
            $command->setLastRunAt(new \DateTime());
            $this->entityManager->flush();

            return Result::ACK;
        } catch (ProcessFailedException $e) {

            $message = sprintf('<error> The process failed with exception: "%s" in %s at %s', $e->getMessage(), $e->getFile(), $e->getLine());
            $command->setLastResult($message);
            $this->entityManager->flush();

            return Result::reject($message);
        }
    }

    public static function getSubscribedCommand()
    {
        return [
            'processorName' => self::COMMAND_NAME,
            'queueName' => self::COMMAND_NAME,
            'queueNameHardcoded' => true,
//            'exclusive' => true,
        ];
    }
}
