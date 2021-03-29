# JwtBundle
This [Symfony](https://symfony.com/) bundle allows developers to generate and validate any
[JSON Web Tokens](https://tools.ietf.org/html/rfc7519) (JWTs) using convenient services.

It leverages power of the widely-used, robust "[lcobucci/jwt](https://github.com/lcobucci/jwt)"
library, which provides most of the functionality, security and reliability.


## Installation
Like any other Symfony bundle, use Composer to install it:
```sh
composer require php-solution/jwt-bundle
```

Symfony Flex should auto-register the bundle for you.
If it doesn't, modify your `bundles.php` file:

```PHP
return [
    // ...
    PhpSolution\JwtBundle\JwtBundle::class => ['all' => true],
];
```


## Configuration
This bundle supports sets of configurations and sets of JWT Token Types.

Configurations define signing and verification options, token types are presets for simple
JWT tokens that you can use in your code. For more advanced token setups
[specify JWT types as services](#specify-jwt-types-as-services).

### Token signing configuration

If you generate and consume the JWT tokens only yourself, you can use a symmetric key.
Otherwise you probably want to use public-key (aka asymmetric) cryptography for signing and verification.

````YAML
// config/packages/jwt.yaml
jwt:
  default_configuration: 'default'
  configurations:
    default: # name 
      asymmetric: true
      signer:
        class: 'Lcobucci\JWT\Signer\Rsa\Sha512'
      signing_key:
        content: 'file://%kernel.project_dir%/config/secrets/jwt/key.pem'
        pass: 'test'
      verification_key:
        content: 'file://%kernel.project_dir%/config/secrets/jwt/key.pub.pem'
````

For detailed documentation about the signing process and the signer classes see
the [JWT library documentation](https://lcobucci-jwt.readthedocs.io/en/latest/configuration/).

You can also specify custom services for `signer`, `signing_key` and `verification_key`.
This gives you full control over signing and verification:

````YAML
// config/packages/jwt.yaml
jwt:
  default_configuration: 'default'
  configurations:
    default: # name 
      signer:
        service_id: 'jwt_signer_service_id'
      signing_key: 'jwt_signing_key_service_id'
      verification_key: 'jwt_verification_key_service_id'
````


#### Generating asymmetric signing and verification key

You can use the following shell commands to generate a public-key pair
suitable for use with this bundle:

````sh
mkdir -p config/secrets/jwt
openssl genrsa -out config/secrets/jwt/key.pem -aes256 4096
openssl rsa -pubout -in config/secrets/jwt/key.pem -out config/secrets/jwt/key.pub.pem
````

Note that you probably don't want to commit the private key (especially if you don't specify a password).
You can use [Symfony Secrets](https://symfony.com/doc/current/configuration/secrets.html)
to to store the file(s) and/or the decryption key.


### Configure JWT Types
You can specify JWT Type on your basic config.yaml.
If configuration is null, system set default configuration

````YAML
// config/packages/jwt.yaml
jwt:  
  types:
    authorization: #name of type
      configuration: 'default'
      options:
        exr: 0
        issued_at: 0
        used_after: 0
        claims: []
        headers: []
        issuer: ''
        id: ''
        audience: ''
        subject: ''
````

using on controller:

````PHP
// src/Controller/UserConfirmController.php

use PhpSolution\JwtBundle\Jwt\TokenManagerInterface;

class UserConfirmController extends Controller
{
    private TokenManagerInterface $tokenManager;

    public function __construct(TokenManagerInterface $tokenManager){
        $this->tokenManager = $tokenManager;
    }

    public function sendLinkAction(): Response
    {
        $token = $this->tokenManager->create('authorization', ['claim' => 'value']);
        $jwtStr = $token->toString(); // this is your encoded JWT token
    }

    public function confirmAction(string $token): Response
    {
        $token = $this->tokenManager->parseTokenWithClaims($token, 'authorization', ['claim']);
        $userId = $token->claims()->get('claim');
    }
}
````


## Specify JWT Types as services

Create the token type, making sure *at the very least* the *SignedWith* constraint
is returned by `getConstraints` - otherwise your token will be unsafe and not verified:

````PHP
// src/Service/UserConfirm.php

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Builder as BuilderInterface;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Validation\Constraint;
use PhpSolution\JwtBundle\Jwt\Type\TypeInterface;

class UserConfirm implements TypeInterface
{
    private const EXP_TIME = 3600;
    public const NAME = 'user_confirm_registration';

    public function getName(): string
    {
        return self::NAME;
    }

    public function configureBuilder(BuilderInterface $builder): void
    {
        $builder->expiresAt(new \DateTimeImmutable('+' . self::EXP_TIME . 'second'));
    }

    public function getConstraints(Configuration $config):? iterable
    {
        yield new Constraint\SignedWith($config->getSigner(), $config->getVerificationKey());
        yield new Constraint\ValidAt(SystemClock::fromSystemTimezone());
    }
}
````

If you use autoconfiguration, implementing the TypeInterface automatically tags the service for you.
Otherwise tag the service manually:

````YAML
// config/services.yaml
services:
    jwt.type.user_confirm_registration:
        class: 'App\Services\JwtType\UserConfirmReg'
        tags: ['jwt.token_type']
````

Then use it somewhere - like in a controller:

````PHP
<?php
// src/Controller/UserConfirmController.php

use App\Services\JwtType\UserConfirm;
use PhpSolution\JwtBundle\Jwt\TokenManagerInterface;

class UserConfirmController extends Controller
{
    private TokenManagerInterface $tokenManager;

    public function __construct(TokenManagerInterface $tokenManager){
        $this->tokenManager = $tokenManager;
    }

    public function sendLinkAction(): Response
    {
        $token = $this->tokenManager->create(UserConfirm::NAME, ['user_id' => $userId]);
        $jwtStr = $token->toString(); // this is your encoded JWT token
    }

    public function confirmAction(string $token): Response
    {
        $token = $this->tokenManager->parseTokenWithClaims($token, UserConfirm::NAME, ['user_id']);
        $userId = $token->claims()->get('user_id');
    }
}
````

## Full Default Configuration
````YAML
jwt:
  default_configuration: 'default'
  configurations:
    default:
      asymmetric: true
      signer:
        service_id: ~
        class: 'Lcobucci\JWT\Signer\Rsa\Sha512'
      signing_key:
        service_id: ~
        content: ~
        pass: ~
      verification_key:
        service_id: ~
        content: ~
  types:
    authorization:
      configuration: 'default'
      options:
        exr: ~
        issued_at: ~
        used_after: ~
        claims: []
        headers: []
        issuer: ~
        id: ~
        audience: ~
        subject: ~
````
