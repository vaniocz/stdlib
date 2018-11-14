<?php

namespace Vanio\Stdlib\Tests;

use ErrorException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Vanio\Stdlib\Enum;

interface Bar
{
    public const CORGE  = 'BarCorge';
}

class Foo extends Enum implements Bar
{
    public const BAR = 'FooBar';
    public const BAZ = ['FooBaz'];
    public const QUX = ['FooQux1', 'FooQux2'];
    
    private const QUUX = 'FooQux';

    /** @var string */
    public $value;

    /** @var string|null */
    public $secondValue;

    protected function __construct(string $value, string $secondValue = null)
    {
        parent::__construct();
        $this->value = $value;
        $this->secondValue = $secondValue;
    }
}

class EnumTest extends TestCase
{
    function test_value_can_be_instantiated_by_its_name()
    {
        $this->assertInstanceOf(Foo::class, Foo::BAR());
    }

    function test_for_the_same_name_the_same_instance_is_always_returned()
    {
        $this->assertSame(Foo::BAR(), Foo::BAR());
    }

    function test_for_different_name_a_different_instance_is_returned()
    {
        $this->assertNotSame(Foo::BAR(), Foo::BAZ());
    }

    function test_only_defined_values_can_be_instantiated()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Enum Vanio\Stdlib\Tests\Foo does not contain value named BOGUS.');

        Foo::BOGUS();
    }

    function test_values_are_passed_to_enum_constructor()
    {
        $this->assertSame(Foo::BAR, Foo::BAR()->value);
        $this->assertSame(Foo::BAZ, [Foo::BAZ()->value]);
        $this->assertSame(Foo::QUX, [Foo::QUX()->value, Foo::QUX()->secondValue]);
    }

    function test_all_available_values_can_be_obtained()
    {
        $this->assertSame([Foo::BAR(), Foo::BAZ(), Foo::QUX()], iterator_to_array(Foo::values()));
    }

    function test_enum_values_have_correct_names()
    {
        $this->assertSame('BAR', Foo::BAR()->name());
        $this->assertSame('BAZ', Foo::BAZ()->name());
    }

    function test_valueOf_returns_the_same_value_as_callStatic()
    {
        $this->assertSame(Foo::BAR(), Foo::valueOf('BAR'));
    }

    function test_values_can_be_casted_to_strings()
    {
        $this->assertSame('BAR', (string) Foo::BAR());
        $this->assertSame('BAZ', (string) Foo::BAZ());
    }

    function test_enum_values_cannot_be_cloned()
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('There can be only one instance for each enumeration value.');

        clone Foo::BAR();
    }

    function test_enum_value_can_be_unboxed()
    {
        $this->assertSame(Foo::BAR, Foo::BAR()->unbox());
    }

    function test_plain_value_can_be_boxed()
    {
        $this->assertSame(Foo::BAR(), Foo::box(Foo::BAR));
        $this->assertSame(Foo::QUX(), Foo::box(Foo::QUX));
    }

    function test_method_create_is_alias_of_box()
    {
        $this->assertSame(Foo::box(Foo::BAR), Foo::create(Foo::BAR));
    }

    function test_plain_values_not_within_enumeration_cannot_be_boxed()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value 123 is not within Vanio\Stdlib\Tests\Foo enumeration.');

        Foo::box(123);
    }

    function test_enum_values_cannot_be_deserialized()
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Enumeration values cannot be deserialized.');

        unserialize('O:22:"Vanio\Stdlib\Tests\Foo":1:{s:4:"name";s:3:"BAR";}');
    }

    function test_get_names_of_all_values()
    {
        $this->assertSame(['BAR', 'BAZ', 'QUX'], Foo::valueNames());
    }

    function test_get_all_plain_values()
    {
        $this->assertSame(['FooBar', ['FooBaz'], ['FooQux1', 'FooQux2']], iterator_to_array(Foo::plainValues()));
    }
}
