<?php

namespace Vanio\Stdlib;

abstract class Arrays
{
    /**
     * @param array $array
     * @param string|int|array $path
     * @param mixed $default
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public static function get(array $array, $path, $default = null)
    {
        foreach ((array) $path as $key) {
            if (!is_array($array) || !array_key_exists($key, $array)) {
                if (func_num_args() < 3) {
                    throw new \InvalidArgumentException(sprintf('The array is missing path %s.', json_encode($path)));
                }

                return $default;
            }

            $array = &$array[$key];
        }

        return $array;
    }

    /**
     * @param array &$array
     * @param string|int|array $path
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public static function &getReference(array &$array, $path)
    {
        foreach ((array) $path as $key) {
            if ($array !== null && !is_array($array)) {
                throw new \InvalidArgumentException(sprintf(
                    'Traversing the array at path %s contains non-array element.',
                    json_encode($path)
                ));
            }

            $array = &$array[$key];
        }

        return $array;
    }

    /**
     * @param array &$array
     * @param string|int|array $path
     * @param mixed $value
     */
    public static function set(array &$array, $path, $value)
    {
        $reference = &self::getReference($array, $path);
        $reference = $value;
    }

    /**
     * @param array &$array
     * @param string|int|array $path
     * @throws \InvalidArgumentException
     */
    public static function unset(array &$array, $path)
    {
        $path = (array) $path;
        $referencePath = $path;
        $key = array_pop($referencePath);
        $array = &self::getReference($array, $referencePath);

        if (!is_array($array)) {
            throw new \InvalidArgumentException(sprintf(
                'Traversing the array at path %s contains non-array element.',
                json_encode($path)
            ));
        }

        unset($array[$key]);
    }
}
