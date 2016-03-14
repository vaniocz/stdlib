<?php

namespace Vanio\Stdlib;

class Objects
{
    /**
     * Prevent instantiating.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {}

    /**
     * Get type of the given $value.
     *
     * @param mixed $value
     * @return string
     */
    public static function getType($value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }

    /**
     * Get value of the given $property in the given $object or class.
     *
     * @param object|string $object an object or a class name
     * @param string $property
     * @param object|string $scope either a class name, object or "static"
     * @return mixed
     */
    public static function &getPropertyValue($object, string $property, $scope = 'static')
    {
        $getPropertyValue = function &() use ($object, $property) {
            if (is_object($object)) {
                return $object->$property;
            } else {
                return $object::$$property;
            }

        };
        $getPropertyValue = $getPropertyValue->bindTo(is_object($object) ? $object : null, $scope);

        return $getPropertyValue();
    }

    /**
     * Set the given $value of the given $property in the given $object or class.
     *
     * @param object|string $object
     * @param string $property
     * @param mixed $value
     * @param object|string $scope either a class name, object or "static"
     */
    public static function setPropertyValue($object, string $property, $value, $scope = 'static')
    {
        $setPropertyValue = function ($value) use ($object, $property) {
            if (is_object($object)) {
                $object->$property  = $value;
            } else {
                $object::$$property = $value;
            }
        };
        $setPropertyValue = $setPropertyValue->bindTo(is_object($object) ? $object : null, $scope);
        $setPropertyValue($value);
    }

    /**
     * Get all constants of the given $class or object optionally prefixed by the given $prefix.
     *
     * @param object|string $class
     * @param string $prefix
     * @return array
     */
    public static function getConstants($class, string $prefix = '')
    {
        static $constants = [];

        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!isset($constants[$class])) {
            $constants[$class][''] = (new \ReflectionClass($class))->getConstants();
        }

        if (!isset($constants[$class][$prefix])) {
            $constants[$class][$prefix] = [];

            foreach ($constants[$class][''] as $name => $value) {
                if (Strings::startsWith($name, $prefix)) {
                    $constants[$class][$prefix][$name] = $value;
                }
            }
        }

        return $constants[$class][$prefix];
    }
}
