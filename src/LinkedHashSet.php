<?php

namespace Vanio\Stdlib;

use Countable;
use IteratorAggregate;
use Traversable;

class LinkedHashSet implements Countable, IteratorAggregate
{
    /** @var LinkedHashMap */
    private $hashMap;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->hashMap = new LinkedHashMap();
    }

    /**
     * Add the given value to the set if it is not already present.
     *
     * @param mixed $value The value to be added.
     *
     * @return bool True if the value was not present in the set, false otherwise.
     */
    public function add($value): bool
    {
        if (isset($this->hashMap[$value])) {
            return false;
        }

        $this->hashMap[$value] = 1;

        return true;
    }

    /**
     * Tells whether or not the given value is present in the set.
     *
     * @param mixed $value
     *
     * @return bool True if the given value is present in the set, false otherwise.
     */
    public function contains($value): bool
    {
        return isset($this->hashMap[$value]);
    }

    /**
     * Remove the given value if it's present in the set.
     *
     * @param mixed $value The value to be removed.
     *
     * @return bool True if the value was present in the set, false otherwise.
     */
    public function remove($value): bool
    {
        if (!isset($this->hashMap[$value])) {
            return false;
        }

        unset($this->hashMap[$value]);

        return true;
    }

    /**
     * Get all the set values.
     *
     * @return mixed[] All the set values.
     */
    public function values(): array
    {
        return $this->hashMap->keys();
    }

    /**
     * Get the number of items in the set.
     *
     * @return int The number of items in the set.
     */
    public function count(): int
    {
        return $this->hashMap->count();
    }

    /**
     * Get the set iterator.
     *
     * @return Traversable The set iterator.
     */
    public function getIterator(): Traversable
    {
        foreach ($this->hashMap->keys() as $value) {
            yield $value;
        }
    }
}
