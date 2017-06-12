<?php

namespace PhpSolution\JwtBundle\Jwt\Type;

/**
 * Class TypeRegistry
 */
class TypeRegistry
{
    /**
     * @var \ArrayObject|TypeInterface[]
     */
    private $registry;
    /**
     * @var array
     */
    private $options = [];

    /**
     * TypeRegistry constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->registry = new \ArrayObject();
        $this->options = $options;
    }

    /**
     * @param TypeInterface $type
     */
    public function addType(TypeInterface $type): void
    {
        $this->registry->offsetSet($type->getName(), $type);
    }

    /**
     * @param string $name
     *
     * @return TypeInterface
     */
    public function getTypeByName(string $name): TypeInterface
    {
        if (!$this->registry->offsetExists($name)) {
            if (array_key_exists($name, $this->options)) {
                $this->addType(new ConfigurableType($name, $this->options[$name]));
            } elseif (class_exists($name) && in_array(TypeInterface::class, class_implements($name))) {
                $this->addType(new $name());
            } else {
                throw new \InvalidArgumentException(sprintf('Undefined JWT type with name:"%s"', $name));
            }
        }

        return $this->registry->offsetGet($name);
    }
}