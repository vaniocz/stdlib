<?php

namespace Vanio\Stdlib\Tests\Fixtures;

class Baz extends Bar
{
    public static $publicStaticValue = 'publicStaticValue';

    private static $privateStaticValue = 'privateStaticValue';

    public $publicValue = 'publicValue';

    private $privateValue = 'privateValue';

    public static function privateStaticValue()
    {
        return self::$privateStaticValue;
    }

    public function privateValue()
    {
        return $this->privateValue;
    }
}
