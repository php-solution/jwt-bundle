<?php

declare(strict_types=1);

namespace PhpSolution\JwtBundle\Jwt\Exception;

use RuntimeException;
use Throwable;

class InvalidTokenType extends RuntimeException implements \Lcobucci\JWT\Exception
{
}
