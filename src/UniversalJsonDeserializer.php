<?php

namespace Vanio\Stdlib;

use Closure;
use ReflectionClass;
use Serializable;

class UniversalJsonDeserializer
{
    /**
     * Deserialize the given JSON string.
     *
     * @param string $json The JSON string to be deserialized.
     *
     * @return mixed The deserialized value/array/object.
     */
    public static function deserialize(string $json)
    {
        $deserialized = json_decode($json, true);
        $objects = [];

        return self::decode($deserialized, $objects);
    }

    /**
     * Decode the given normalized data to a concrete value.
     *
     * @param mixed $data The data to be decoded.
     * @param object[] $objects The set of objects that were deserialized so far.
     *
     * @return mixed The decoded value.
     */
    private static function &decode(&$data, array &$objects)
    {
        if ($data === null || is_scalar($data)) {
            return $data;
        }

        if (isset($data['μ'])) {
            return self::provideObject($data, $objects);
        }

        return self::decodeArray($data, $objects);
    }

    /**
     * Provide the object corresponding with the given data.
     *
     * @param mixed[] $data The object data.
     * @param object[] $objects The set of objects that were deserialized so far.
     *
     * @return object The object corresponding with the given data.
     */
    private static function &provideObject(array &$data, array &$objects)
    {
        $class = $data['μ']['fqn'];
        $id = $data['μ']['#'];

        if (method_exists($class, '__set_state')) {
            $objects[$id] = $class::{'__set_state'}($data);

            return $objects[$id];
        }

        if (isset($objects[$id])) {
            return $objects[$id];
        }

        unset($data['μ']);
        $reflection = self::reflect($class);
        $objects[$id] = $object = $reflection->newInstanceWithoutConstructor();

        if ($object instanceof Serializable) {
            $object->unserialize($data['$']);
            return $object;
        }

        self::setObjectProperties($object, $data, $reflection->isInternal(), function &(&$value) use (&$objects) {
            return self::decode($value, $objects);
        });

        if (method_exists($object, '__wakeup')) {
            $object->__wakeup();
        }

        return $object;
    }

    /**
     * Get the reflection of the given class.
     *
     * @param string $class The name of the class that should be reflected.
     *
     * @return ReflectionClass The reflection of the given class.
     */
    private static function reflect(string &$class): ReflectionClass
    {
        static $reflections = [];

        return $reflections[$class] ?? $reflections[$class] = new ReflectionClass($class);
    }

    /**
     * Set the given object properties.
     *
     * @param mixed $object The object which properties should be set.
     * @param mixed[] $properties The object properties.
     * @param bool $isInternal Tells whether the object class is internal.
     * @param Closure $propertyDecoder The object property decoder.
     */
    private static function setObjectProperties($object, array &$properties, bool $isInternal, Closure $propertyDecoder)
    {
        Closure::bind(function () use ($object, &$properties, $propertyDecoder) {
            foreach ($properties as $key => &$value) {
                $object->$key = $propertyDecoder($value);
            }
        }, null, $isInternal ? null : $object)->__invoke();
    }

    /**
     * Decode the given array of normalized values.
     *
     * @param mixed[] $array The array to be decoded.
     * @param object[] $objects The set of objects that were deserialized so far.
     *
     * @return mixed[] The decoded array.
     */
    private static function &decodeArray(array &$array, array &$objects): array
    {
        foreach ($array as &$value) {
            $value = self::decode($value, $objects);
        }

        return $array;
    }
}
