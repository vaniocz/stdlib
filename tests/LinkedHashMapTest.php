<?php

namespace Vanio\Stdlib\Tests;

use PHPUnit_Framework_TestCase;
use Vanio\Stdlib\LinkedHashMap;

class LinkedHashMapTest extends PHPUnit_Framework_TestCase
{
    /** @var LinkedHashMap */
    private $map;

    function setUp()
    {
        $this->map = new LinkedHashMap();
    }

    function test_added_value_with_string_key_can_be_retrieved_by_its_key()
    {
        $this->map['test'] = 'Hello!';

        $this->assertSame('Hello!', $this->map['test']);
    }

    function test_added_value_with_numeric_key_can_be_retrieved_by_its_key()
    {
        $this->map[0] = 'First';
        $this->map[1.02] = 'Second';

        $this->assertSame('First', $this->map[0]);
        $this->assertSame('Second', $this->map[1.02]);
    }

    function test_added_value_with_object_key_can_be_retrieved_by_its_key()
    {
        $key = (object) ['name' => 'John'];
        $this->map[$key] = 'Hello John!';

        $this->assertSame('Hello John!', $this->map[$key]);
    }

    function test_added_value_with_array_key_can_be_retrieved_by_its_key()
    {
        $key = [
            'name' => 'John',
            'surname' => 'Doe',
            'attrs' => ['gender' => 'male'],
            'info' => (object) ['desc' => 'none'],
        ];
        $this->map[$key] = 'Hello John!';

        $this->assertSame('Hello John!', $this->map[$key]);
    }

    function test_key_existence_within_the_map_can_be_determined()
    {
        $this->map['test'] = 'Hello!';

        $this->assertFalse(isset($this->map['bogus']));
        $this->assertTrue(isset($this->map['test']));
    }

    function test_value_can_be_removed_from_the_map_by_its_key()
    {
        $this->map['test'] = 'Hello!';
        unset($this->map['test']);

        $this->assertFalse(isset($this->map['test']));
    }

    function test_map_size_is_zero_at_the_beginning()
    {
        $this->assertSame(0, count($this->map));
    }

    function test_map_size_can_be_determined()
    {
        $this->map[0] = 'Hello!';
        $this->assertSame(1, count($this->map));
    }

    function test_map_can_be_iterated_through()
    {
        $objectKey = (object) ['data' => ['something']];
        $expected = [
            [0, 'test0'],
            [1, 'test1'],
            [1.01, 'test1.01'],
            ['foo', 'bar'],
            [['name' => 'John', 'surname' => 'Doe'], 'John Doe'],
            [$objectKey, 'Object']
        ];

        foreach ($expected as list($key, $value)) {
            $this->map[$key] = $value;
        }

        $index = 0;
        foreach ($this->map as $key => $value) {
            $this->assertSame($expected[$index][0], $key);
            $this->assertSame($expected[$index++][1], $value);
        }
    }

    function test_all_the_map_keys_can_be_retrieved()
    {
        $keys = [0, 1, 1.01, 'foo', ['name' => 'John', 'surname' => 'Doe'], (object) ['data' => ['something']]];

        foreach ($keys as $i => $key) {
            $this->map[$key] = $i;
        }

        $this->assertSame($keys, $this->map->keys());
    }

    function test_there_cannot_be_duplicate_keys_in_a_map()
    {
        $this->map['foo'] = 0;
        $this->map['foo'] = 1;

        $this->assertSame(['foo'], $this->map->keys());
    }

    function test_value_with_same_key_overwrites_value_inserted_previously()
    {
        $this->map['foo'] = 0;
        $this->map['foo'] = 1;

        $this->assertSame(1, $this->map['foo']);
    }

    function test_when_appending_value_without_a_key_the_key_is_max_integer_key_plus_one()
    {
        $items = [1 => 'foo', 2 => 'bar', 'foo' => 'bar', 4 => 666];

        foreach ($items as $key => $value) {
            $this->map[$key] = $value;
        }
        $this->map[] = 123;

        $this->assertSame([1, 2, 'foo', 4, 5], $this->map->keys());
        $this->assertSame(['foo', 'bar', 'bar', 666, 123], $this->map->values());
    }

    function test_when_appending_value_without_a_key_and_all_integer_keys_are_negative_than_the_key_is_zero()
    {
        $items = [-2 => 'foo', -3 => 'bar', 'foo' => 'bar', -4 => 666];

        foreach ($items as $key => $value) {
            $this->map[$key] = $value;
        }
        $this->map[] = 123;

        $this->assertSame([-2, -3, 'foo', -4, 0], $this->map->keys());
        $this->assertSame(['foo', 'bar', 'bar', 666, 123], $this->map->values());
    }

    function test_when_appending_value_without_a_key_and_the_map_is_empty_than_the_key_is_zero()
    {
        $this->map[] = 1;

        $this->assertSame([0], $this->map->keys());
    }

    function test_when_appending_value_without_a_key_and_there_is_no_integer_key_than_the_key_is_zero()
    {
        $this->map['foo'] = 'bar';
        $this->map[] = 1;

        $this->assertSame(['foo', 0], $this->map->keys());
    }

    function test_all_the_map_values_can_be_retrieved()
    {
        $values = [0, 1, 1.01, 'foo', ['name' => 'John', 'surname' => 'Doe'], (object) ['data' => ['something']]];

        foreach ($values as $value) {
            $this->map[] = $value;
        }

        $this->assertSame($values, $this->map->values());
    }

    function test_map_is_correctly_initialized_after_deserialization()
    {
        $objectKey = (object) ['data' => ['something']];
        $expected = [
            [0, 'test0'],
            [1, 'test1'],
            [1.01, 'test1.01'],
            ['foo', 'bar'],
            [['name' => 'John', 'surname' => 'Doe'], 'John Doe'],
            [$objectKey, 'Object']
        ];

        foreach ($expected as list($key, $value)) {
            $this->map[$key] = $value;
        }

        $this->map = unserialize(serialize($this->map));

        $index = 0;
        foreach ($this->map->keys() as $key) {
            $this->assertEquals($expected[$index][0], $key);
            $this->assertSame($expected[$index++][1], $this->map[$key]);
        }
    }
}
