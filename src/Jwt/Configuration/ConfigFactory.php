<?php

namespace PhpSolution\JwtBundle\Jwt\Configuration;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;

/**
 * Class ConfigFactory
 */
class ConfigFactory
{
    public const OPTION_SIGNER = 'signer';
    public const OPTION_SIGNKEY_CONTENT = 'signkey_content';
    public const OPTION_SIGNKEY_PASS = 'signkey_pass';
    public const OPTION_VERKEY_CONTENT = 'verkey_content';

    /**
     * @var string
     */
    private $name;
    /**
     * @var bool
     */
    private $asymmetric = true;
    /**
     * @var array
     */
    private $options = [];
    /**
     * @var Signer
     */
    private $signer;
    /**
     * @var Key
     */
    private $signingKey;
    /**
     * @var Key
     */
    private $verificationKey;

    /**
     * ConfigFactory constructor.
     *
     * @param string $name
     * @param bool   $asymmetric
     * @param array  $options
     */
    public function __construct(string $name, bool $asymmetric = true, array $options = [])
    {
        $this->name = $name;
        $this->asymmetric = $asymmetric;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Configuration
     */
    public function createConfiguration(): Configuration
    {
        return $this->asymmetric
            ? Configuration::forAsymmetricSigner($this->getSigner(), $this->getSigningKey(), $this->getVerificationKey())
            : Configuration::forSymmetricSigner($this->getSigner(), $this->getSigningKey());
    }

    /**
     * @param Signer $signer
     */
    public function setSigner(Signer $signer): void
    {
        $this->signer = $signer;
    }

    /**
     * @param Key $signingKey
     */
    public function setSigningKey(Key $signingKey): void
    {
        $this->signingKey = $signingKey;
    }

    /**
     * @param Key $verificationKey
     */
    public function setVerificationKey(Key $verificationKey): void
    {
        $this->verificationKey = $verificationKey;
    }

    /**
     * @return Signer
     */
    private function getSigner(): Signer
    {
        return $this->signer ?: $this->signer = new $this->options[self::OPTION_SIGNER];
    }

    /**
     * @return Key
     */
    private function getSigningKey(): Key
    {
        return $this->signingKey
            ?: $this->signingKey = Key\InMemory::plainText(
                $this->options[self::OPTION_SIGNKEY_CONTENT] ?? '',
                $this->options[self::OPTION_SIGNKEY_PASS] ?? ''
            );
    }

    /**
     * @return Key
     */
    private function getVerificationKey(): Key
    {
        return $this->verificationKey
            ?: $this->verificationKey = Key\InMemory::plainText($this->options[self::OPTION_VERKEY_CONTENT] ?? '');
    }
}