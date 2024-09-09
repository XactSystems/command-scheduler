<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('xact_command_scheduler');
        $rootNode = $treeBuilder->getRootNode();
        assert($rootNode instanceof ArrayNodeDefinition);

        $rootNode
            ->children()
                ->booleanNode('clear_data')->defaultTrue()->end()
                ->booleanNode('retry_on_fail')->defaultFalse()->end()
                ->integerNode('retry_delay')->defaultValue(60)->end()
                ->integerNode('retry_max_attempts')->defaultValue(60)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
