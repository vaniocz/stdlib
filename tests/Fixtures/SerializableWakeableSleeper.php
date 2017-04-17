<?php

namespace Vanio\Stdlib\Tests\Fixtures;

use Serializable;

class SerializableWakeableSleeper extends WakeableSleeper implements Serializable
{
    /** @var int */
    private $numberOfSerializeCalls = 0;

    /** @var int */
    private $numberOfUnserializeCalls = 0;

    /** @var string */
    private $data;

    public function serialize(): string
    {
        ++$this->numberOfSerializeCalls;

        return '__serialized__';
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        ++$this->numberOfUnserializeCalls;
        $this->data = $serialized;
    }

    public function numberOfSerializeCalls(): int
    {
        return $this->numberOfSerializeCalls;
    }

    public function numberOfUnserializeCalls(): int
    {
        return $this->numberOfUnserializeCalls;
    }

    public function data(): string
    {
        return $this->data;
    }
}
