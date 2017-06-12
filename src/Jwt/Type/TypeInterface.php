<?php

namespace PhpSolution\JwtBundle\Jwt\Type;

use Lcobucci\JWT\Builder as BuilderInterface;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Validation\Constraint;

/**
 * Class TypeInterface
 */
interface TypeInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param BuilderInterface $builder
     */
    public function configureBuilder(BuilderInterface $builder): void;

    /**
     * @param Configuration $config
     *
     * @return iterable|Constraint[]
     */
    public function getConstraints(Configuration $config):? iterable;
}