<?php

namespace Vanio\Stdlib\Tests;

use PHPUnit_Framework_TestCase;
use stdClass;
use Vanio\Stdlib\Tests\Fixtures\Bar;
use Vanio\Stdlib\UniversalJsonDeserializer as Deserializer;

class UniversalJsonDeserializerTest extends PHPUnit_Framework_TestCase
{
    function test_single_scalar_value_is_deserialized_correctly()
    {
        $this->assertSame('test', Deserializer::deserialize('"test"'));
    }

    function test_null_is_deserialized_correctly()
    {
        $this->assertNull(Deserializer::deserialize('null'));
    }

    function test_arrays_are_deserialized_correctly()
    {
        $this->assertSame([1, 'foo'], Deserializer::deserialize('[1,"foo"]'));
    }

    function test_empty_objects_are_deserialized_correctly()
    {
        $object = Deserializer::deserialize('{"μ":{"#":0,"fqn":"stdClass"}}');

        $this->assertInstanceOf(stdClass::class, $object);
        $this->assertCount(0, (array) $object);
    }

    function test_objects_are_deserialized_correctly()
    {
        $json = '{'
            . '"foo":"bar",'
            . '"baz":["qux"],'
            . '"obj":{'
                . '"publicValue":"parentPublicValue",'
                . '"privateValue":"parentPrivateValue",'
                . '"μ":{"#":1,"fqn":"Vanio\\\Stdlib\\\Tests\\\Fixtures\\\Bar"}'
            . '},'
            . '"μ":{"#":0,"fqn":"stdClass"}' .
        '}';

        $object = Deserializer::deserialize($json);

        $this->assertInstanceOf(stdClass::class, $object);
        $this->assertCount(3, (array) $object);
        $this->assertSame('bar', $object->foo);
        $this->assertSame(['qux'], $object->baz);
        $this->assertInstanceOf(Bar::class, $object->obj);
        $this->assertSame('parentPublicValue', $object->obj->publicValue);
        $this->assertSame('parentPrivateValue', $object->obj->privateValue());
    }

    function test_array_of_objects_is_deserialized_correctly()
    {
        $array = Deserializer::deserialize('[{"μ":{"#":0,"fqn":"stdClass"}},{"μ":{"#":1,"fqn":"stdClass"}}]');

        $this->assertCount(2, $array);
        $this->assertContainsOnlyInstancesOf(stdClass::class, $array);
    }

    function test_object_references_are_deserialized_correctly()
    {
        $json = '[{"μ":{"#":0,"fqn":"stdClass"}},{"foo":{"μ":{"#":0,"fqn":"stdClass"}},"μ":{"#":1,"fqn":"stdClass"}}]';
        $objects = Deserializer::deserialize($json);

        $this->assertSame($objects[0], $objects[1]->foo);
    }
}
