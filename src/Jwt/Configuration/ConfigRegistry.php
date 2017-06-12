<?php

namespace PhpSolution\JwtBundle\Jwt\Configuration;

use Lcobucci\JWT\Configuration;

/**
 * Class ConfigRegistry
 */
class ConfigRegistry
{
    /**
     * @var \ArrayObject|ConfigFactory[]
     */
    private $factoryRegistry;
    /**
     * @var \ArrayObject|Configuration[]
     */
    private $configRegistry;
    /**
     * @var Configuration
     */
    private $defaultConfig;
    /**
     * @var string
     */
    private $defaultConfigName;

    /**
     * ConfigRegistry constructor.
     *
     * @param string $defaultConfigName
     */
    public function __construct(?string $defaultConfigName)
    {
        $this->defaultConfigName = $defaultConfigName;
        $this->factoryRegistry = new \ArrayObject();
        $this->configRegistry = new \ArrayObject();
    }

    /**
     * @param ConfigFactory $configFactory
     */
    public function addConfigFactory(ConfigFactory $configFactory): void
    {
        $this->factoryRegistry->offsetSet($configFactory->getName(), $configFactory);
    }

    /**
     * @param string $name
     *
     * @return Configuration
     */
    public function getConfiguration(string $name): Configuration
    {
        if (!$this->configRegistry->offsetExists($name)) {
            if (!$this->factoryRegistry->offsetExists($name)) {
                throw new \InvalidArgumentException(sprintf('Undefined JWT config factory with name="%s"', $name));
            }
            /* @var $factory ConfigFactory */
            $factory = $this->factoryRegistry->offsetGet($name);
            $config = $factory->createConfiguration();
            $this->configRegistry->offsetSet($name, $config);
        }

        return $this->configRegistry->offsetGet($name);
    }

    /**
     * @return Configuration
     */
    public function getDefaultConfiguration(): Configuration
    {
        return $this->defaultConfig ?: $this->defaultConfig = $this->getConfiguration($this->defaultConfigName);
    }
}