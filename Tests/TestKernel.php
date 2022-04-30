<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\Tests;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', true);
    }

    /**
     * @inheritDoc
     */
    public function registerBundles()
    {
        $bundles = [
            \Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class,
            \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class,
            \Symfony\Bundle\FrameworkBundle\FrameworkBundle::class,
            \Symfony\Bundle\TwigBundle\TwigBundle::class,
            \Xact\CommandScheduler\XactCommandSchedulerBundle::class,
        ];

        foreach ($bundles as $class) {
            yield new $class();
        }
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getProjectDir() . '/Resources/config';
        $routes->import($confDir . '/routing.yaml');
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $confDir = $this->getProjectDir() . '/Resources/config';
        $loader->load($confDir . '/{test}/*.yaml', 'glob');
    }
}
