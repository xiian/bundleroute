<?php

declare(strict_types=1);

namespace Xiian\BundleRoute;

use OutOfBoundsException;

class MissingMappingException extends OutOfBoundsException
{
    public static function forMapping(string $mapping): self
    {
        return new self('No mapping found for ' . $mapping);
    }
}
