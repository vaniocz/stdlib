<?php

namespace Vanio\Stdlib\Tests\Fixtures;

class Bar
{
    const CONST_FOO = 'const_foo';
    const CONST_BAR = 'const_bar';
    const CONSTANT = 'constant';

    /** @var string */
    public static $publicStaticValue = 'parentPublicStaticValue';

    /** @var string */
    private static $privateStaticValue = 'parentPrivateStaticValue';

    /** @var string */
    public $publicValue = 'parentPublicValue';

    /** @var string */
    private $privateValue = 'parentPrivateValue';

    public static function privateStaticValue(): string
    {
        return self::$privateStaticValue;
    }

    public function privateValue(): string
    {
        return $this->privateValue;
    }
}
