<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\Service;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

class CommandLister
{
    private KernelInterface $kernel;


    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return array<string, string|null>
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
