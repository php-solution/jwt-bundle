<?php

namespace PhpSolution\JwtBundle\Jwt\Type;

use Lcobucci\JWT\Builder as BuilderInterface;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Validation\Constraint;

/**
 * Class BasicType
 */
class BasicType implements TypeInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * @param BuilderInterface $builder
     */
    public function configureBuilder(BuilderInterface $builder): void
    {
    }

    /**
     * @param Configuration $config
     *
     * @return iterable|null
     */
    public function getConstraints(Configuration $config):? iterable
    {
        yield new Constraint\SignedWith($config->getSigner(), $config->getVerificationKey());
    }
}