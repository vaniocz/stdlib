<?php

namespace Vanio\Stdlib\Tests\Fixtures;

class WakeableSleeper
{
    /** @var static */
    private $myself;

    /** @var static */
    private $twin;

    /** @var int */
    private $numberOfSleepCalls = 0;

    /** @var int */
    private $numberOfWakeUpCalls = 0;

    public function __construct()
    {
        $this->myself = [$this];
        $this->twin = clone $this;
        $this->twin->twin = $this;
    }

    public function __sleep(): array
    {
        ++$this->numberOfSleepCalls;

        return ['myself', 'twin'];
    }

    public function __wakeup()
    {
        ++$this->numberOfWakeUpCalls;
    }

    public function numberOfSleepCalls(): int
    {
        return $this->numberOfSleepCalls;
    }

    public function numberOfWakeUpCalls(): int
    {
        return $this->numberOfWakeUpCalls;
    }

    public function myself(): self
    {
        return $this->myself;
    }

    public function twin(): self
    {
        return $this->twin;
    }
}
