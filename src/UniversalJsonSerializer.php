<?php

namespace Vanio\Stdlib;

use Closure;
use Serializable;
use SplObjectStorage;
use Throwable;

/**
 * Can serialize any serializable PHP value into a JSON encoded string.
 */
class UniversalJsonSerializer
{
    /**
     * Serialize the given value into a JSON encoded string.
     *
     * @param mixed $value The value to be serialized.
     *
     * @return string The JSON encoded string.
     */
    public static function serialize($value): string
    {
        return json_encode(
            self::encode($value, new SplObjectStorage()),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Encode the given value into a normalized structure.
     *
     * @param mixed $value The value to be encoded into a normalized structure.
     * @param SplObjectStorage $objectIds The set of IDs of all currently processed objects.
     *
     * @return mixed The normalized structure.
     */
    private static function encode($value, SplObjectStorage $objectIds)
    {
        $encoded = [$value];
        array_walk_recursive($encoded, function (&$value) use ($objectIds) {
            if (!is_object($value)) {
                return;
            }

            $isReference = isset($objectIds[$value]);

            $meta = [
                '#' => !$isReference ? $objectIds[$value] = count($objectIds) : $objectIds[$value],
                'fqn' => get_class($value),
            ];

            $value = ($isReference ? [] : self::encode(self::objectProperties($value), $objectIds)) + ['μ' => $meta];
        });

        return $encoded[0];
    }

    /**
     * Get array of all the object properties.
     *
     * @param object $object
     *
     * @return mixed[] The array of all the object properties.
     */
    private static function objectProperties($object): array
    {
        try {
            $properties = Closure::bind(function () use ($object) {
                return get_object_vars($object);
            }, null, $object)->__invoke();
        } catch (Throwable $e) {
            $properties = get_object_vars($object);
        }

        if ($object instanceof Serializable) {
            $properties = ['$' => $object->serialize()];
        } elseif (method_exists($object, '__sleep')) {
            $properties = array_intersect_key($properties, array_flip($object->__sleep()));
        }

        return $properties;
    }
}
