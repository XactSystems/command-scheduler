<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class XactCommandSchedulerExtension extends ConfigurableExtension
{
    /**
     * @param array<string, bool|int|string> $mergedConfig
     */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        $container->setParameter('xact_command_scheduler.clear_data', $mergedConfig['clear_data']);
        $container->setParameter('xact_command_scheduler.retry_on_fail', $mergedConfig['retry_on_fail']);
        $container->setParameter('xact_command_scheduler.retry_delay', $mergedConfig['retry_delay']);
        $container->setParameter('xact_command_scheduler.retry_max_attempts', $mergedConfig['retry_max_attempts']);
    }
}
