<?php

namespace Vanio\Stdlib\Tests;

use PHPUnit_Framework_TestCase;
use Vanio\Stdlib\Objects;
use Vanio\Stdlib\Tests\Fixtures\Bar;
use Vanio\Stdlib\Tests\Fixtures\Baz;

class ObjectsTest extends PHPUnit_Framework_TestCase
{
    function test_getting_type()
    {
        $this->assertEquals('string', Objects::getType('foo'));
        $this->assertEquals('integer', Objects::getType(1));
        $this->assertEquals(__CLASS__, Objects::getType($this));
    }

    function test_getting_value_of_public_property()
    {
        $this->assertSame('publicValue', Objects::getPropertyValue(new Baz(), 'publicValue'));
        $this->assertSame('publicValue', Objects::getPropertyValue(new Baz, 'publicValue', Bar::class));
        $this->assertSame('publicValue', Objects::getPropertyValue(new Baz, 'publicValue', Baz::class));

        $this->assertSame('parentPublicValue', Objects::getPropertyValue(new Bar, 'publicValue'));
        $this->assertSame('parentPublicValue', Objects::getPropertyValue(new Bar, 'publicValue', Bar::class));
        $this->assertSame('parentPublicValue', Objects::getPropertyValue(new Bar, 'publicValue', Baz::class));

        $this->assertNull(Objects::getPropertyValue(new Baz, 'nonDeclaredProperty'));
    }

    function test_cannot_get_value_of_non_existent_static_property()
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Access to undeclared static property');
        Objects::getPropertyValue(Bar::class, 'nonExistentProperty');
    }

    function test_getting_value_of_public_static_property()
    {
        $this->assertSame('publicStaticValue', Objects::getPropertyValue(Baz::class, 'publicStaticValue'));
        $this->assertSame('publicStaticValue', Objects::getPropertyValue(Baz::class, 'publicStaticValue', Bar::class));
        $this->assertSame('publicStaticValue', Objects::getPropertyValue(Baz::class, 'publicStaticValue', Baz::class));

        $this->assertSame('parentPublicStaticValue', Objects::getPropertyValue(Bar::class, 'publicStaticValue'));
        $this->assertSame('parentPublicStaticValue', Objects::getPropertyValue(Bar::class, 'publicStaticValue', Bar::class));
        $this->assertSame('parentPublicStaticValue', Objects::getPropertyValue(Bar::class, 'publicStaticValue', Baz::class));
    }

    function test_cannot_get_value_of_private_property_without_specifying_scope()
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot access private property');
        Objects::getPropertyValue(new Baz, 'privateValue');
    }

    function test_getting_value_of_private_property()
    {
        $this->assertSame('privateValue', Objects::getPropertyValue(new Baz, 'privateValue', Baz::class));
        $this->assertSame('parentPrivateValue', Objects::getPropertyValue(new Baz, 'privateValue', Bar::class));
        $this->assertSame('parentPrivateValue', Objects::getPropertyValue(new Bar, 'privateValue', Bar::class));
    }

    function test_cannot_get_value_of_private_static_property_without_specifying_scope()
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot access private property');
        Objects::getPropertyValue(Baz::class, 'privateStaticValue');
    }

    function test_getting_value_of_private_static_property()
    {
        $this->assertSame(
            'privateStaticValue',
            Objects::getPropertyValue(Baz::class, 'privateStaticValue', Baz::class)
        );
        $this->assertSame(
            'parentPrivateStaticValue',
            Objects::getPropertyValue(Bar::class, 'privateStaticValue', Bar::class)
        );
    }

    function test_getting_value_of_public_property_by_reference()
    {
        $object = new Baz;
        $value = &Objects::getPropertyValue($object, 'publicValue');
        $value = 'value';
        $this->assertSame('value', $object->publicValue);
    }

    function test_getting_value_of_public_static_property_by_reference()
    {
        $value = &Objects::getPropertyValue(Baz::class, 'publicStaticValue');
        $value = 'value';
        $this->assertSame('value', Baz::$publicStaticValue);
    }

    function test_getting_value_of_private_property_by_reference()
    {
        $object = new Baz;
        $value = &Objects::getPropertyValue($object, 'privateValue', Baz::class);
        $value = 'value';
        $this->assertSame('value', $object->privateValue());
    }

    function test_getting_value_of_private_static_property_by_reference()
    {
        $value = &Objects::getPropertyValue(Baz::class, 'privateStaticValue', Baz::class);
        $value = 'value';
        $this->assertSame('value', Baz::privateStaticValue());
    }

    function test_setting_value_of_public_property()
    {
        $object = new Baz;
        Objects::setPropertyValue($object, 'publicValue', 'value');
        $this->assertSame('value', $object->publicValue);

        $object = new Baz;
        Objects::setPropertyValue($object, 'publicValue', 'value', Bar::class);
        $this->assertSame('value', $object->publicValue);

        $object = new Baz;
        Objects::setPropertyValue($object, 'publicValue', 'value', Baz::class);
        $this->assertSame('value', $object->publicValue);

        $object = new Baz;
        Objects::setPropertyValue($object, 'nonDeclaredProperty', 'value');
        $this->assertSame('value', $object->{'nonDeclaredProperty'});
    }

    function test_cannot_set_value_of_non_existent_static_property()
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Access to undeclared static property');
        Objects::setPropertyValue(Bar::class, 'nonExistentProperty', 'value');
    }

    function test_setting_value_of_public_static_property()
    {
        Objects::setPropertyValue(Baz::class, 'publicStaticValue', 'value');
        $this->assertSame('value', Baz::$publicStaticValue);

        Objects::setPropertyValue(Baz::class, 'publicStaticValue', 'value', Bar::class);
        $this->assertSame('value', Baz::$publicStaticValue);

        Objects::setPropertyValue(Baz::class, 'publicStaticValue', 'value', Baz::class);
        $this->assertSame('value', Baz::$publicStaticValue);
    }

    function test_cannot_set_value_of_private_property_without_specifying_scope()
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot access private property');
        Objects::setPropertyValue(new Baz, 'privateValue', 'value');
    }

    function test_setting_value_of_private_property()
    {
        $object = new Baz;
        Objects::setPropertyValue($object, 'privateValue', 'value', Baz::class);
        $this->assertSame('value', $object->privateValue());

        $object = new Baz;
        Objects::setPropertyValue($object, 'privateValue', 'value', Bar::class);
        $this->assertSame('value', Objects::getPropertyValue($object, 'privateValue', Bar::class));
    }

    function test_cannot_set_value_of_private_static_property_without_specifying_scope()
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot access private property');
        Objects::setPropertyValue(Baz::class, 'privateStaticValue', 'value');
    }

    function test_setting_value_of_private_static_property()
    {
        Objects::setPropertyValue(Baz::class, 'privateStaticValue', 'value', Baz::class);
        $this->assertSame('value', Baz::privateStaticValue());
    }

    function test_getting_class_constants()
    {
        $this->assertSame(
            ['CONST_FOO' => 'const_foo', 'CONST_BAR' => 'const_bar', 'CONSTANT' => 'constant'],
            Objects::getConstants(new Bar)
        );
        $this->assertSame(
            ['CONST_FOO' => 'const_foo', 'CONST_BAR' => 'const_bar'],
            Objects::getConstants(Bar::class, 'CONST_')
        );
    }
}
