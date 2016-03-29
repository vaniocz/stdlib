<?php

namespace Vanio\Stdlib\Tests;

use PHPUnit_Framework_TestCase;
use stdClass;
use Vanio\Stdlib\Tests\Fixtures\Bar;
use Vanio\Stdlib\UniversalJsonSerializer as Serializer;

class UniversalJsonSerializerTest extends PHPUnit_Framework_TestCase
{
    function test_single_scalar_value_is_serialized_correctly()
    {
        $this->assertSame('"test"', Serializer::serialize('test'));
    }

    function test_null_is_serialized_correctly()
    {
        $this->assertSame('null', Serializer::serialize(null));
    }

    function test_arrays_are_serialized_correctly()
    {
        $this->assertSame('[1,"foo"]', Serializer::serialize([1, 'foo']));
    }

    function test_empty_objects_are_serialized_correctly()
    {
        $this->assertSame('{"μ":{"#":0,"fqn":"stdClass"}}', Serializer::serialize(new stdClass()));
    }

    function test_objects_are_serialized_correctly()
    {
        $this->assertSame(
            '{'
                . '"foo":"bar",'
                . '"baz":["qux"],'
                . '"obj":{'
                    . '"publicValue":"parentPublicValue",'
                    . '"privateValue":"parentPrivateValue",'
                    . '"μ":{"#":1,"fqn":"Vanio\\\Stdlib\\\Tests\\\Fixtures\\\Bar"}'
                . '},'
                . '"μ":{"#":0,"fqn":"stdClass"}' .
            '}',
            Serializer::serialize((object) [
                'foo' => 'bar',
                'baz' => ['qux'],
                'obj' => new Bar(),
            ])
        );
    }

    function test_array_of_objects_is_serialized_correctly()
    {
        $this->assertSame(
            '[{"μ":{"#":0,"fqn":"stdClass"}},{"μ":{"#":1,"fqn":"stdClass"}}]',
            Serializer::serialize([new stdClass(), new stdClass()])
        );
    }

    function test_object_references_are_detected_and_their_ids_determined()
    {
        $foo = new stdClass();
        $bar = new stdClass();
        $bar->foo = $foo;

        $this->assertSame(
            '[{"μ":{"#":0,"fqn":"stdClass"}},{"foo":{"μ":{"#":0,"fqn":"stdClass"}},"μ":{"#":1,"fqn":"stdClass"}}]',
            Serializer::serialize([$foo, $bar])
        );
    }
}
