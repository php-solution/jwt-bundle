<?php

declare(strict_types=1);

namespace PhpSolution\JwtBundle\Jwt;


use Lcobucci\JWT\Exception;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;

/**
 * Class JwtTokenManager
 */
interface TokenManagerInterface
{
    /**
     * @param string $typeName
     * @param iterable $claims
     * @param iterable $headers
     *
     * @return Token\Plain
     */
    public function create(string $typeName, iterable $claims = [], iterable $headers = []): Token\Plain;

    /**
     * @param string $jwt
     * @param string $typeName
     *
     * @return Token
     *
     * @throws Exception
     */
    public function parse(string $jwt, string $typeName): Token;

    /**
     * @param string $tokenStr
     * @param string $tokenType
     * @param array $requiredClaims
     *
     * @return UnencryptedToken
     * @throws Exception
     */
    public function parseTokenWithClaims(string $tokenStr, string $tokenType, array $requiredClaims): UnencryptedToken;
}
