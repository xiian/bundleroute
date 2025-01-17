<?php

declare(strict_types=1);

namespace Xiian\BundleRoute;

class StringUtils
{
    public static function getPrefixForStrings(array $strings): string
    {
        if (count($strings) <= 1) {
            return '';
        }

        $prefix = $strings[0];
        foreach ($strings as $key) {
            while (!str_starts_with($key, $prefix)) {
                $prefix = substr($prefix, 0, -1);
                if ($prefix === '') {
                    break 2;
                }
            }
        }

        return $prefix;
    }
}
