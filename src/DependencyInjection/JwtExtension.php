<?php

namespace PhpSolution\JwtBundle\DependencyInjection;

use PhpSolution\JwtBundle\Jwt\Configuration\ConfigFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class JwtExtension
 */
class JwtExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->registerConfigurations($config, $container);
        $this->registerTypes($config, $container);
    }

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    private function registerConfigurations(array $configs, ContainerBuilder $container): void
    {
        $registryDef = $container->getDefinition('jwt.configuration_registry');
        $registryDef->replaceArgument(0, $configs['default_configuration']);

        foreach ($configs['configurations'] as $conf) {
            $def = (new Definition(ConfigFactory::class))
                ->setPublic(false)
                ->setArgument(0, $conf['name'])
                ->setArgument(1, $conf['asymmetric'])
                ->setArgument(2, [
                        ConfigFactory::OPTION_SIGNER => $conf['signer']['class'],
                        ConfigFactory::OPTION_SIGNKEY_CONTENT => $conf['signing_key']['content'],
                        ConfigFactory::OPTION_SIGNKEY_PASS => $conf['signing_key']['pass'],
                        ConfigFactory::OPTION_VERKEY_CONTENT => $conf['verification_key']['content'],
                    ]
                );
            if (isset($conf['signer']['service_id'])) {
                $def->addMethodCall('setSigner', [new Reference($conf['signer']['service_id'])]);
            }
            if (isset($conf['signing_key']['service_id'])) {
                $def->addMethodCall('setSigningKey', [new Reference($conf['signing_key']['service_id'])]);
            }
            if (isset($conf['verification_key']['service_id'])) {
                $def->addMethodCall('setVerificationKey', [new Reference($conf['signer']['service_id'])]);
            }

            $id = 'jwt.configuration.' . $conf['name'];
            $container->setDefinition($id, $def);
            $registryDef->addMethodCall('addConfigFactory', [new Reference($id)]);
        }
    }

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    private function registerTypes(array $configs, ContainerBuilder $container): void
    {
        $container->getDefinition('jwt.token_type_registry')
            ->setArgument(0, $configs['types']);
    }
}