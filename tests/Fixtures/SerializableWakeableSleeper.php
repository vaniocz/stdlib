<?php

namespace Vanio\Stdlib\Tests\Fixtures;

use Serializable;

class SerializableWakeableSleeper extends WakeableSleeper implements Serializable
{
    private $numberOfSerializeCalls = 0;

    private $numberOfUnserializeCalls = 0;

    public function serialize(): string
    {
        ++$this->numberOfSerializeCalls;

        return '__serialized__';
    }

    public function unserialize($serialized)
    {
        ++$this->numberOfUnserializeCalls;

        return '__unserialized__';
    }

    public function numberOfSerializeCalls(): int
    {
        return $this->numberOfSerializeCalls;
    }

    public function numberOfUnserializeCalls(): int
    {
        return $this->numberOfUnserializeCalls;
    }
}
