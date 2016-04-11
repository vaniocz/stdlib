<?php

namespace Vanio\Stdlib;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;

class LinkedHashMap implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The set of all keys of the map.
     *
     * @var mixed[]
     */
    private $keys = [];

    /**
     * The list of all values of the map.
     *
     * @var mixed[]
     */
    private $values = [];

    /**
     * Maps keys to the value set indices.
     *
     * @var int[]
     */
    private $keysIndices = [];

    /**
     * Tells whether or not the given key exists within the map.
     *
     * @param mixed $key
     *
     * @return bool True if the given key exists within the map, false otherwise.
     */
    public function offsetExists($key): bool
    {
        return isset($this->keysIndices[self::keyString($key)]);
    }

    /**
     * Retrieve a value with the given key.
     *
     * @param mixed $key
     *
     * @return mixed A value with the given key.
     */
    public function offsetGet($key)
    {
        if (($index = $this->keysIndices[self::keyString($key)] ?? null) === null) {
            return null;
        }

        return $this->values[$index];
    }

    /**
     * Insert the given value with the given key.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    {
        if ($key === null) {
            $intKeys = array_filter($this->keys, 'is_int');
            $this->keys[] = $i = empty($intKeys) ? 0 : max(max($intKeys) + 1, 0);
            $this->values[] = $value;
            $this->keysIndices[$i] = count($this->values) - 1;
        } elseif (($index = $this->keysIndices[self::keyString($key)] ?? null) === null) {
            $this->keys[] = $key;
            $this->values[] = $value;
            $this->keysIndices[self::keyString($key)] = count($this->values) - 1;
        } else {
            $this->values[$index] = $value;
        }
    }

    /**
     * Remove a value with the given key from the map.
     *
     * @param mixed $key
     */
    public function offsetUnset($key)
    {
        $keyString = self::keyString($key);
        if (($index = $this->keysIndices[$keyString] ?? null) !== null) {
            unset($this->keys[$index], $this->values[$index], $this->keysIndices[$keyString]);
        }
    }

    /**
     * Get the number of items in the map.
     *
     * @return int The number of items in the map.
     */
    public function count(): int
    {
        return count($this->values);
    }

    /**
     * Get the map iterator.
     *
     * @return Traversable The map iterator.
     */
    public function getIterator(): Traversable
    {
        foreach ($this->values as $index => &$value) {
            yield $this->keys[$index] => $value;
        }
    }

    /**
     * Get all the map keys.
     *
     * @return mixed[] All the map keys.
     */
    public function keys(): array
    {
        return $this->keys;
    }

    /**
     * Get all the map values.
     *
     * @return mixed[] All the map values.
     */
    public function values(): array
    {
        return $this->values;
    }

    /**
     * Get the list of properties which should be serialized.
     *
     * @return string[] The list of properties which should be serialized.
     */
    public function __sleep(): array
    {
        return ['keys', 'values'];
    }

    /**
     * Initialize the map after deserialization.
     */
    public function __wakeup()
    {
        $this->keysIndices = array_flip(array_map('self::keyString', $this->keys));
    }

    /**
     * Calculate a string representing the given key.
     *
     * @param mixed $key
     *
     * @return string A string representing the given key.
     */
    private static function keyString($key): string
    {
        return $key === null || is_scalar($key)
            ? (string) $key
            : (is_object($key) ? spl_object_hash($key) : self::arrayKeyString($key));
    }

    /**
     * Calculate a string representing the given key.
     *
     * @param mixed[] $key
     *
     * @return string A string representing the given key.
     */
    private static function arrayKeyString(array $key): string
    {
        ksort($key);
        foreach ($key as &$value) {
            if (is_array($value)) {
                $value = self::arrayKeyString($value);
            } elseif (is_object($value)) {
                $value = self::keyString($value);
            }
        }

        return json_encode($key);
    }
}
