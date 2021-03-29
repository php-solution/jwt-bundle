<?php

namespace PhpSolution\JwtBundle\Jwt\Type;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Builder as BuilderInterface;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Validation\Constraint;

/**
 * Class ConfigurableType
 */
class ConfigurableType extends BasicType implements TypeConfigInterface
{
    public const OPTIONS = [
        self::OPTION_CLAIMS,
        self::OPTION_HEADERS,
        self::OPTION_ISSUER,
        self::OPTION_ISSUED_AT,
        self::OPTION_EXP,
        self::OPTION_USED_AFTER,
        self::OPTION_ID,
        self::OPTION_AUDIENCE,
        self::OPTION_SUBJECT,
    ];
    public const OPTION_CLAIMS = 'claimes';
    public const OPTION_HEADERS = 'headers';
    public const OPTION_ISSUER = 'issuer';
    public const OPTION_ISSUED_AT = 'issued_at';
    public const OPTION_EXP = 'exp';
    public const OPTION_USED_AFTER = 'used_after';
    public const OPTION_ID = 'id';
    public const OPTION_AUDIENCE = 'audience';
    public const OPTION_SUBJECT = 'subject';

    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $configurationName;
    /**
     * @var array
     */
    protected $options;
    /**
     * @var array
     */
    protected $constraints = [];
    /**
     * @var array|\Closure[]
     */
    private static $handlers;

    /**
     * ConfigurableType constructor.
     *
     * @param string      $name
     * @param array       $options
     * @param string|null $configurationName
     */
    public function __construct(string $name, ?string $configurationName, array $options = [])
    {
        $this->name = $name;
        $this->options = $options;
        $this->configurationName = $configurationName;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getConfigurationName():? string
    {
        return $this->configurationName;
    }

    /**
     * @param BuilderInterface $builder
     */
    public function configureBuilder(BuilderInterface $builder): void
    {
        $handlers = self::$handlers ?: self::$handlers = $this->createBuilderHandlers();
        foreach ($this->options as $name => $value) {
            if (!empty($value) && array_key_exists($name, $handlers)) {
                $handlers[$name]($builder, $value);
            }
        }
    }

    /**
     * @param Configuration $config
     *
     * @return iterable|null
     */
    public function getConstraints(Configuration $config):? iterable
    {
        yield from parent::getConstraints($config);
        yield new Constraint\LooseValidAt(SystemClock::fromSystemTimezone());
        if (array_key_exists(static::OPTION_SUBJECT, $this->options) && !empty($this->options[static::OPTION_SUBJECT])) {
            yield new Constraint\RelatedTo($this->options[static::OPTION_SUBJECT]);
        }
        if (array_key_exists(static::OPTION_AUDIENCE, $this->options) && !empty($this->options[static::OPTION_AUDIENCE])) {
            yield new Constraint\PermittedFor($this->options[static::OPTION_AUDIENCE]);
        }
        if (array_key_exists(static::OPTION_ISSUER, $this->options) && !empty($this->options[static::OPTION_ISSUER])) {
            yield new Constraint\IssuedBy($this->options[static::OPTION_ISSUER]);
        }
        if (array_key_exists(static::OPTION_ID, $this->options) && !empty($this->options[static::OPTION_ID])) {
            yield new Constraint\IdentifiedBy($this->options[static::OPTION_ID]);
        }
    }

    /**
     * @param integer $configValue
     *
     * @return \DateTimeImmutable
     */
    private function getTime(int $configValue): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())->setTimestamp(\time() + $configValue);
    }

    /**
     * @return array
     */
    private function createBuilderHandlers(): array
    {
        return [
            self::OPTION_CLAIMS => function (BuilderInterface $builder, $configValue) {
                foreach ((array) $configValue as $name => $val) {
                    $builder->withClaim($name, $val);
                }
            },
            self::OPTION_HEADERS => function (BuilderInterface $builder, $configValue) {
                foreach ((array) $configValue as $name => $val) {
                    $builder->withHeader($name, $val);
                }
            },
            static::OPTION_ISSUER => function (BuilderInterface $builder, $configValue) {
                $builder->issuedBy((string) $configValue);
            },
            static::OPTION_ID => function (BuilderInterface $builder, $configValue) {
                $builder->identifiedBy((string) $configValue);
            },
            static::OPTION_AUDIENCE => function (BuilderInterface $builder, $configValue) {
                $builder->permittedFor((string) $configValue);
            },
            static::OPTION_SUBJECT => function (BuilderInterface $builder, $configValue) {
                $builder->relatedTo((string) $configValue);
            },
            static::OPTION_ISSUED_AT => function (BuilderInterface $builder, $configValue) {
                $builder->issuedAt($this->getTime($configValue));
            },
            static::OPTION_USED_AFTER => function (BuilderInterface $builder, $configValue) {
                $builder->canOnlyBeUsedAfter($this->getTime($configValue));
            },
            static::OPTION_EXP => function (BuilderInterface $builder, $configValue) {
                $builder->expiresAt($this->getTime($configValue));
            },
        ];
    }
}
