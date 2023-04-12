<?php

namespace Vanio\Stdlib;

use Closure;
use DateTimeInterface;
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
        $serialized = json_encode(
            self::encode($value, new SplObjectStorage()),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }

        return $serialized;
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
            $class = get_class($value);
            $meta = [
                '#' => !$isReference ? $objectIds[$value] = count($objectIds) : $objectIds[$value],
                'fqn' => $class,
            ];

            if ($isReference && !method_exists($class, '__set_state')) {
                $value = [];
            } else {
                $value = self::encode(self::objectProperties($value), $objectIds);
            }

            $value += ['μ' => $meta];
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
        if ($object instanceof Serializable) {
            return ['$' => $object->serialize()];
        }

        if ($object instanceof DateTimeInterface) {
            return json_decode(json_encode($object), true);
        }

        $class = get_class($object);
        $properties = [];

        do {
            try {
                $properties += Closure::bind(function () use ($object) {
                    return get_object_vars($object);
                }, null, $class)->__invoke();
            } catch (Throwable $e) {
                return get_object_vars($object);
            }
        } while ($class = get_parent_class($class));

        return method_exists($object, '__sleep')
            ? array_intersect_key($properties, array_flip($object->__sleep()))
            : $properties;
    }
}
