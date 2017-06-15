<?php

namespace PhpSolution\JwtBundle\DependencyInjection;

use PhpSolution\JwtBundle\Jwt\Type\ConfigurableType;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('jwt');
        $this->addConfigurationSection($rootNode);
        $this->addConfigurableTypeSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addConfigurationSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->scalarNode('default_configuration')->isRequired()->defaultValue('default')->end()
                ->arrayNode('configurations')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->fixXmlConfig('configuration')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('name')->defaultValue('default')->end()
                            ->booleanNode('asymmetric')->defaultTrue()->end()
                            ->arrayNode('signer')
                                ->canBeUnset()
                                ->validate()
                                    ->ifTrue(function ($v) { return !isset($v['service_id']) && !isset($v['class']); })
                                    ->thenInvalid('Please set service_id or class for Signer')
                                ->end()
                                ->children()
                                    ->scalarNode('service_id')->defaultNull()->end()
                                    ->scalarNode('class')->defaultNull()->end()
                                ->end()
                            ->end()
                            ->arrayNode('signing_key')
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(function ($v) { return ['service_id' => $v]; })
                                ->end()
                                ->children()
                                    ->scalarNode('service_id')->defaultNull()->end()
                                    ->scalarNode('content')->defaultNull()->end()
                                    ->scalarNode('pass')->defaultNull()->end()
                                ->end()
                            ->end()
                            ->arrayNode('verification_key')
                                ->canBeUnset()
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(function ($v) { return ['service_id' => $v]; })
                                ->end()
                                ->children()
                                    ->scalarNode('service_id')->defaultNull()->end()
                                    ->scalarNode('content')->defaultNull()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addConfigurableTypeSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('types')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->fixXmlConfig('configurable_type')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode(ConfigurableType::OPTION_CONF_NAME)->defaultValue('default')->end()
                            ->arrayNode(ConfigurableType::OPTION_HEADERS)
                                ->canBeUnset()
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode(ConfigurableType::OPTION_CLAIMS)
                                ->canBeUnset()
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode(ConfigurableType::OPTION_SUBJECT)->defaultNull()->end()
                            ->scalarNode(ConfigurableType::OPTION_AUDIENCE)->defaultNull()->end()
                            ->scalarNode(ConfigurableType::OPTION_ID)->defaultNull()->end()
                            ->scalarNode(ConfigurableType::OPTION_ISSUER)->defaultNull()->end()
                            ->integerNode(ConfigurableType::OPTION_EXP)->defaultNull()->end()
                            ->integerNode(ConfigurableType::OPTION_USED_AFTER)->defaultNull()->end()
                            ->integerNode(ConfigurableType::OPTION_ISSUED_AT)->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}