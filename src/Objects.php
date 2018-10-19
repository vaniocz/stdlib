<?php

namespace Vanio\Stdlib;

class Objects
{
    /**
     * Prevent instantiating.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
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
                $object->$property = $value;
            } else {
                $object::$$property = $value;
            }
        };
        $setPropertyValue = $setPropertyValue->bindTo(is_object($object) ? $object : null, $scope);
        $setPropertyValue($value);
    }

    /**
     * Creates an instance of the given $class using the given $factoryMethod passing the given arguments or named
     * $parameters
     *
     * @param string $class
     * @param mixed[]  $parameters
     * @return object
     */
    public static function create(string $class, array $parameters, string $factoryMethod = '__construct')
    {
        return self::call($class, $factoryMethod, $parameters);
    }

    /**
     * Calls the given $method on the given $object or class passing the given arguments or named $parameters.
     *
     * @param object|string $object
     * @param string $method
     * @param mixed[] $parameters
     * @return object
     */
    public static function call($object, string $method, array $parameters)
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionMethod = $reflectionClass->getMethod($method);
        $arguments = [];

        foreach ($reflectionMethod->getParameters() as $i => $reflectionParameter) {
            $argument = null;

            if (array_key_exists($reflectionParameter->name, $parameters) || array_key_exists($i, $parameters)) {
                $argument = $parameters[$reflectionParameter->name] ?? $parameters[$i] ?? null;
            } elseif ($reflectionParameter->isDefaultValueAvailable()) {
                $argument = $reflectionParameter->getDefaultValue();
            }

            if ($reflectionType = $reflectionParameter->getType()) {
                if ($reflectionType->isBuiltin()) {
                    if ($argument !== null || !$reflectionType->allowsNull()) {
                        settype($argument, $reflectionType->getName());
                    }
                } elseif ($argument === null && !$reflectionType->allowsNull()) {
                    return null;
                }
            }

            $arguments[] = $argument;
        }

        if ($reflectionMethod->isConstructor()) {
            return $reflectionClass->newInstanceArgs($arguments);
        }

        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($reflectionMethod->isStatic() ? null : $object, $arguments);
    }

    /**
     * Get all constants of the given $class or object optionally prefixed by the given $prefix.
     *
     * @param object|string $class
     * @param string $prefix
     * @return array
     */
    public static function getConstants($class, string $prefix = ''): array
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

    /**
     * @param array|object $object
     * @param callable $callback
     * @param string[] $visitedObjects
     */
    public static function walk(&$object, callable $callback, array $visitedObjects = [])
    {
        array_walk($object, function (&$value, string $property) use ($object, $callback, $visitedObjects) {
            if (is_object($value)) {
                $hash = spl_object_hash($value);

                if (isset($visitedObjects[$hash])) {
                    return;
                }

                $visitedObjects[$hash] = true;
            }

            if (call_user_func_array($callback, [&$value, $property, $object]) !== false) {
                if (is_array($value) || is_object($value)) {
                    self::walk($value, $callback, $visitedObjects);
                }
            }
        });
    }
}
