<?php

namespace Vanio\Stdlib\Tests;

use PHPUnit_Framework_TestCase;
use stdClass;
use Vanio\Stdlib\Tests\Fixtures\Bar;
use Vanio\Stdlib\Tests\Fixtures\SerializableWakeableSleeper;
use Vanio\Stdlib\Tests\Fixtures\WakeableSleeper;
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

    function test_serializer_can_handle_circular_references()
    {
        $foo = new stdClass();
        $bar = new stdClass();
        $baz = new stdClass();

        $foo->bar = [$bar];
        $bar->foo = [$foo];
        $bar->qux = 'qux';
        $baz->foo = $foo;
        $baz->bar = $bar;

        $str = '[{'
                . '"bar":[{"foo":[{"μ":{"#":0,"fqn":"stdClass"}}],"qux":"qux","μ":{"#":1,"fqn":"stdClass"}}],'
                . '"μ":{"#":0,"fqn":"stdClass"}' .
                '},' .
                '{'
                . '"qux":"qux",'
                . '"μ":{"#":1,"fqn":"stdClass"}' .
                '},' .
                '{'
                . '"foo":{"μ":{"#":0,"fqn":"stdClass"}},'
                . '"bar":{"qux":"qux","μ":{"#":1,"fqn":"stdClass"}},'
                . '"μ":{"#":2,"fqn":"stdClass"}' .
                '}]';

        $this->assertSame($str, Serializer::serialize([$foo, $bar, $baz]));
    }

    function test_magic_method_sleep_is_called_on_all_objects()
    {
        $mock = $this->getMockForAbstractClass(WakeableSleeper::class);
        $mock->expects($this->exactly(2))->method('__sleep')->willReturn(['myself']);

        $this->assertSame(
            sprintf('{"myself":[{"μ":{"#":0,"fqn":"%s"}}],"μ":{"#":0,"fqn":"%s"}}', get_class($mock), get_class($mock)),
            Serializer::serialize($mock)
        );
    }

    function test_method_serialize_is_called_when_object_implements_serializable()
    {
        $mock = $this->getMockForAbstractClass(SerializableWakeableSleeper::class);
        $mock->expects($this->once())->method('serialize')->willReturn('__foo_bar__');

        $this->assertSame(
            sprintf('{"$":"__foo_bar__","μ":{"#":0,"fqn":"%s"}}', get_class($mock), get_class($mock)),
            Serializer::serialize($mock)
        );
    }

    function test_magic_method_sleep_is_not_called_when_object_implements_serializable()
    {
        $mock = $this->getMockForAbstractClass(SerializableWakeableSleeper::class);
        $mock->expects($this->never())->method('__sleep');

        Serializer::serialize($mock);
    }
}
