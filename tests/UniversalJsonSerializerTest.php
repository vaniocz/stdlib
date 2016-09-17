<?php

namespace Vanio\Stdlib\Tests;

use PHPUnit\Framework\TestCase;
use stdClass;
use Vanio\Stdlib\Tests\Fixtures\Bar;
use Vanio\Stdlib\Tests\Fixtures\SerializableWakeableSleeper;
use Vanio\Stdlib\Tests\Fixtures\WakeableSleeper;
use Vanio\Stdlib\UniversalJsonSerializer as Serializer;

class UniversalJsonSerializerTest extends TestCase
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
                . '"μ":{"#":1,"fqn":"stdClass"}' .
                '},' .
                '{'
                . '"foo":{"μ":{"#":0,"fqn":"stdClass"}},'
                . '"bar":{"μ":{"#":1,"fqn":"stdClass"}},'
                . '"μ":{"#":2,"fqn":"stdClass"}' .
                '}]';

        $this->assertSame($str, Serializer::serialize([$foo, $bar, $baz]));
    }

    function test_magic_method_sleep_is_called_on_all_objects()
    {
        $sleeper = new WakeableSleeper();

        $serialized = Serializer::serialize($sleeper);

        $str = '{'
                    . '"myself":[{"μ":{"#":0,"fqn":"Vanio\\\Stdlib\\\Tests\\\Fixtures\\\WakeableSleeper"}}],'
                    . '"twin":{'
                        . '"myself":[{"μ":{"#":0,"fqn":"Vanio\\\Stdlib\\\Tests\\\Fixtures\\\WakeableSleeper"}}],'
                        . '"twin":{"μ":{"#":0,"fqn":"Vanio\\\Stdlib\\\Tests\\\Fixtures\\\WakeableSleeper"}},'
                        . '"μ":{"#":1,"fqn":"Vanio\\\Stdlib\\\Tests\\\Fixtures\\\WakeableSleeper"}'
                    . '},'
                    . '"μ":{"#":0,"fqn":"Vanio\\\Stdlib\\\Tests\\\Fixtures\\\WakeableSleeper"}' .
                '}';

        $this->assertSame($str, $serialized);
    }

    function test_magic_method_sleep_is_called_on_all_objects_just_once()
    {
        $sleeper = new WakeableSleeper();

        Serializer::serialize($sleeper);

        $this->assertSame(1, $sleeper->numberOfSleepCalls());
        $this->assertSame(1, $sleeper->twin()->numberOfSleepCalls());
    }

    function test_method_serialize_is_called_when_object_implements_serializable()
    {
        $sleeper = new SerializableWakeableSleeper();

        $this->assertSame(
            '{"$":"__serialized__","μ":{"#":0,"fqn":"Vanio\\\Stdlib\\\Tests\\\Fixtures\\\SerializableWakeableSleeper"}}',
            Serializer::serialize($sleeper)
        );

        $this->assertSame(1, $sleeper->numberOfSerializeCalls());
    }

    function test_magic_method_sleep_is_not_called_when_object_implements_serializable()
    {
        $sleeper = new SerializableWakeableSleeper();

        Serializer::serialize($sleeper);

        $this->assertSame(0, $sleeper->numberOfSleepCalls());
    }
}
