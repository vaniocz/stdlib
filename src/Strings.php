<?php

namespace Vanio\Stdlib;

class Strings
{
    /**
     * Prevent instantiating.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {}

    /**
     * Find out whether the given $string starts with any of the given $values.
     *
     * @param string $string
     * @param string|string[] $values
     * @return bool
     */
    public static function startsWith(string $string, $values): bool
    {
        foreach ((array) $values as $value) {
            if (!strncmp($string, $value, strlen($value))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find out whether the given $string ends with any of the given $values.
     *
     * @param string $string
     * @param string|string[] $values
     * @return bool
     */
    public static function endsWith(string $string, $values)
    {
        foreach ((array) $values as $value) {
            if ($value === '' || substr($string, -strlen($value)) === $value) {
                return true;
            }
        }

        return false;
    }


    /**
     * Find out whether the given $string contains any of the given $values.
     *
     * @param string $string
     * @param string|string[] $values
     * @return bool
     */
    public static function contains($string, $values): bool
    {
        foreach ((array) $values as $value) {
            if (strpos($string, $value) !== false) {
                return true;
            }
        }

        return false;
    }
}
