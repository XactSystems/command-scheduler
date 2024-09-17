<?php

declare(strict_types=1);

namespace Xact\CommandScheduler;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class XactCommandSchedulerBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->booleanNode('clear_data')
                    ->info('Clear the command data after running the command. Default true')
                    ->defaultTrue()
                ->end()
                ->booleanNode('retry_on_fail')
                    ->info('Retry the command on failure. Default false')
                    ->defaultFalse()
                ->end()
                ->integerNode('retry_delay')
                    ->info('The retry period to wait in seconds, Default 60')
                    ->defaultValue(60)
                ->end()
                ->integerNode('retry_max_attempts')
                    ->info('The maximum number of reties to attempt. Default 60')
                    ->defaultValue(60)
                ->end()
            ->end()
        ;
    }

    /**
     * @param array<string, mixed> $config
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()
            ->set('xact_command_scheduler.clear_data', $config['clear_data'])
            ->set('xact_command_scheduler.retry_on_fail', $config['retry_on_fail'])
            ->set('xact_command_scheduler.retry_delay', $config['retry_delay'])
            ->set('xact_command_scheduler.retry_max_attempts', $config['retry_max_attempts'])
        ;
    }

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
    }
}
