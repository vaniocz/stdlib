<?php

namespace Vanio\Stdlib\Tests\Fixtures;

class Baz extends Bar
{
    /** @var string */
    public static $publicStaticValue = 'publicStaticValue';

    /** @var string */
    private static $privateStaticValue = 'privateStaticValue';

    /** @var string */
    public $publicValue = 'publicValue';

    /** @var string */
    private $privateValue = 'privateValue';

    public static function privateStaticValue(): string
    {
        return self::$privateStaticValue;
    }

    public function privateValue(): string
    {
        return $this->privateValue;
    }
}
