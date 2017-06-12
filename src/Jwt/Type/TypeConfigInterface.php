<?php

namespace PhpSolution\JwtBundle\Jwt\Type;

/**
 * Class TypeConfigInterface
 */
interface TypeConfigInterface
{
    /**
     * @return null|string
     */
    public function getConfigurationName():? string;
}