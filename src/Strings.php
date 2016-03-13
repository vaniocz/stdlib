<?php

namespace Vanio\Stdlib;

class Strings
{
    /**
     * Prevent instantiating.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {}

    /**
     * Find out whether the given $string starts with the given $value.
     *
     * @param string $string
     * @param string $value
     * @return bool
     */
    public static function startsWith(string $string, string $value): bool
    {
        return !strncmp($string, $value, strlen($value));
    }

    /**
     * Find out whether the given $string ends with the given $value.
     *
     * @param string $string
     * @param string $value
     * @return bool
     */
    static function endsWith(string $string, string $value)
    {
        return $value === '' || substr($string, -strlen($value)) === $value;
    }


    /**
     * Find out whether the given $string contains the given $value.
     *
     * @param string $string
     * @param string $value
     * @return bool
     */
    public static function contains($string, $value): bool
    {
        return strpos($string, $value) !== false;
    }
}
