<?php

namespace PhpSolution\JwtBundle\Jwt;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Exception;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\ConstraintViolation;
use PhpSolution\JwtBundle\Jwt\Configuration\ConfigRegistry;
use PhpSolution\JwtBundle\Jwt\Exception\InvalidTokenType;
use PhpSolution\JwtBundle\Jwt\Type\TypeConfigInterface;
use PhpSolution\JwtBundle\Jwt\Type\TypeInterface;
use PhpSolution\JwtBundle\Jwt\Type\TypeRegistry;
use function get_class;

/**
 * Class JwtTokenManager
 */
class JwtManager
{
    /**
     * @var ConfigRegistry
     */
    private $configRegistry;
    /**
     * @var TypeRegistry
     */
    private $typeRegistry;

    /**
     * JwtManager constructor.
     *
     * @param ConfigRegistry $configRegistry
     * @param TypeRegistry   $typeRegistry
     */
    public function __construct(ConfigRegistry $configRegistry, TypeRegistry $typeRegistry)
    {
        $this->configRegistry = $configRegistry;
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * @param string   $typeName
     * @param iterable $claims
     * @param iterable $headers
     *
     * @return Token\Plain
     */
    public function create(string $typeName, iterable $claims = [], iterable $headers = []): Token\Plain
    {
        /* @var $type TypeInterface|TypeConfigInterface */
        $type = $this->typeRegistry->getTypeByName($typeName);
        $config = $this->getConfigurationByType($type);
        $builder = $config->builder();
        $builder->issuedAt((new \DateTimeImmutable())->setTimestamp(\time() - 1));
        foreach ($claims as $claimName => $claimValue) {
            $builder->withClaim($claimName, $claimValue);
        }
        foreach ($headers as $headerName => $headerValue) {
            $builder->withHeader($headerName, $headerValue);
        }
        $builder->relatedTo($typeName);
        $type->configureBuilder($builder);

        return $builder->getToken($config->signer(), $config->signingKey());
    }

    /**
     * @param string $jwt
     * @param string $typeName
     *
     * @return Token
     *
     * @throws Exception
     */
    public function parse(string $jwt, string $typeName): Token
    {
        $type = $this->typeRegistry->getTypeByName($typeName);
        $config = $this->getConfigurationByType($type);
        $token = $config->parser()->parse($jwt);

        $constraints = $type->getConstraints($config);
        if (is_iterable($constraints)) {
            $config->validator()->assert($token, ...$constraints);
        }

        return $token;
    }

    /**
     * @param string $tokenStr
     * @param string $tokenType
     * @param array  $requiredClaims
     *
     * @return UnencryptedToken
     * @throws Exception
     */
    public function parseTokenWithClaims(string $tokenStr, string $tokenType, array $requiredClaims): UnencryptedToken
    {
        /* @var $jwtToken UnencryptedToken */
        $jwtToken = $this->parse($tokenStr, $tokenType);
        if (!$jwtToken instanceof UnencryptedToken) {
            throw new InvalidTokenType(sprintf('Token must be an instanceof "%s".', UnencryptedToken::class));
        }

        $claims = $jwtToken->claims();
        foreach ($requiredClaims as $claim) {
            if (!$claims->has($claim)) {
                throw new ConstraintViolation(sprintf('Undefined claim "%s" for token', $claim));
            }
        }

        return $jwtToken;
    }

    /**
     * @param TypeInterface $type
     *
     * @return Configuration
     */
    private function getConfigurationByType(TypeInterface $type): Configuration
    {
        return $type instanceof TypeConfigInterface && !empty($configName = $type->getConfigurationName())
            ? $this->configRegistry->getConfiguration($configName)
            : $this->configRegistry->getDefaultConfiguration();
    }
}
