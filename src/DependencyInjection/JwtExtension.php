<?php

namespace PhpSolution\JwtBundle\DependencyInjection;

use PhpSolution\JwtBundle\Jwt\Configuration\ConfigFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use function array_merge;
use function is_array;

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

        $config = $this->fixOldConfig($config);

        $this->registerConfigurations($config, $container);
        $this->registerTypes($config, $container);
    }

    private function fixOldConfig(array $config): array
    {
        if (!isset($config['types']) || !is_array($config['types'])) {
            return $config;
        }

        foreach ($config['types'] as $type => $typeConfig) {
            if (!isset($typeConfig['options']['claimes']) || !is_array($typeConfig['options']['claimes'])) {
                continue;
            }

            $claims = $typeConfig['options']['claimes'];
            if (isset($typeConfig['options']['claims']) && is_array($typeConfig['options']['claims'])) {
                // new field overrides old values
                $claims = array_merge($claims, $typeConfig['options']['claims']);
            }

            $config['types'][$type]['options']['claims'] = $claims;
            unset($config['types'][$type]['options']['claimes']);
        }

        return $config;
    }

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    private function registerConfigurations(array $configs, ContainerBuilder $container): void
    {
        $registryDef = $container->getDefinition('jwt.configuration_registry');
        $registryDef->setArgument(0, $configs['default_configuration']);

        foreach ($configs['configurations'] as $name => $conf) {
            $def = (new Definition(ConfigFactory::class))
                ->setPublic(false)
                ->setArgument(0, $name)
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

            $id = 'jwt.configuration.' . $name;
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
