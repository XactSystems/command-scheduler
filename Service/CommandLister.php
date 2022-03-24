<?php

namespace Xact\CommandScheduler\Service;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

class CommandLister
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    /**
     * Class constructor.
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return string[]
     */
    public function getCommandChoices(): array
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $commands = $application->all();
        $choices = [];
        foreach ($commands as $command) {
            $choices[$command->getName()] = $command->getName();
        }

        return $choices;
    }
}
