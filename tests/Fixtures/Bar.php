<?php

namespace Vanio\Stdlib\Tests\Fixtures;

class Bar
{
    const CONST_FOO = 'const_foo';
    const CONST_BAR = 'const_bar';
    const CONSTANT = 'constant';

    public static $publicStaticValue = 'parentPublicStaticValue';

    private static $privateStaticValue = 'parentPrivateStaticValue';

    public $publicValue = 'parentPublicValue';

    private $privateValue = 'parentPrivateValue';

    public static function privateStaticValue()
    {
        return self::$privateStaticValue;
    }

    public function privateValue()
    {
        return $this->privateValue;
    }
}
