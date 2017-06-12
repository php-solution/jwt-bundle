<?php

namespace PhpSolution\JwtBundle\Jwt;

/**
 * Class JwtManagerAwareTrait
 */
trait JwtManagerAwareTrait
{
    /**
     * @var JwtManager
     */
    protected $jwtManager;

    /**
     * @param JwtManager $jwtManager
     */
    final public function setJwtManager(JwtManager $jwtManager): void
    {
        $this->jwtManager = $jwtManager;
    }
}