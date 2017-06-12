<?php

namespace PhpSolution\JwtBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class JwtTypesPass
 */
class JwtTypesPass  implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $registryName = 'jwt.token_type_registry';
        if (false === $container->hasDefinition($registryName)) {
            return;
        }

        $registryDef = $container->getDefinition($registryName);
        $taggedServices = $container->findTaggedServiceIds('jwt.token_type');
        if (is_array($taggedServices) && count($taggedServices) > 0) {
            foreach (array_keys($taggedServices) as $id) {
                $registryDef->addMethodCall('addType', [new Reference($id)]);
            }
        }
    }
}