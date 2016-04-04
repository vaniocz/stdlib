<?php

namespace Vanio\Stdlib\Tests\Fixtures;

use Serializable;

abstract class SerializableWakeableSleeper extends WakeableSleeper implements Serializable
{
    abstract public function serialize(): string;

    abstract public function unserialize($serialized);
}
