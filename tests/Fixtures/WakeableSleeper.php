<?php

namespace Vanio\Stdlib\Tests\Fixtures;

abstract class WakeableSleeper
{
    protected $myself;

    public function __construct()
    {
        $this->myself = [$this];
    }

    abstract public function __sleep(): array;

    abstract public function __wakeup();
}
