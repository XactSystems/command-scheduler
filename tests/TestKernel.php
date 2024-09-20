<?php

declare(strict_types=1);

namespace Xact\CommandScheduler\Tests;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', true);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $confDir = $this->getProjectDir() . '/config';
        $loader->load($confDir . '/{test}/*.yaml', 'glob');
    }
}
