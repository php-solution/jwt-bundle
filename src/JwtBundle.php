<?php

namespace PhpSolution\JwtBundle;

use PhpSolution\JwtBundle\DependencyInjection\JwtTypesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class JwtBundle
 */
class JwtBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new JwtTypesPass());
    }
}