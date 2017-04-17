<?php

namespace Vanio\Stdlib\Tests;

use PHPUnit\Framework\TestCase;
use Vanio\Stdlib\Arrays;

class ArraysTest extends TestCase
{
    function test_getting_array_element()
    {
        $array = [
            'foo' => 'bar',
            'baz' => ['qux']
        ];
        $this->assertSame('bar', Arrays::get($array, 'foo'));
        $this->assertSame('qux', Arrays::get($array, ['baz', 0]));
        $this->assertSame(null, Arrays::get($array, 'key', null));
    }

    function test_cannot_get_non_existent_array_element_without_default_value()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The array is missing path ["foo","bar"].');
        Arrays::get([], ['foo', 'bar']);
    }

    function test_getting_array_element_reference()
    {
        $array = [
            'foo' => 'bar',
            'baz' => ['qux']
        ];
        $reference = &Arrays::getReference($array, 'foo');
        $reference = 'foo';
        $reference = &Arrays::getReference($array, ['baz', 0]);
        $reference = 'baz';
        $reference = &Arrays::getReference($array, 'qux');
        $reference = 'qux';
        $this->assertSame([
            'foo' => 'foo',
            'baz' => ['baz'],
            'qux' => 'qux'
        ], $array);
    }

    function test_cannot_get_reference_through_non_array_element()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Traversing the array at path ["foo","bar"] contains non-array element.');
        $array = ['foo' => 'foo'];
        Arrays::getReference($array, ['foo', 'bar']);
    }

    function test_setting_array_element()
    {
        $array = ['foo' => 'bar'];
        Arrays::set($array, 'foo', 'foo');
        Arrays::set($array, ['bar', 0], 'baz');
        $this->assertSame([
            'foo' => 'foo',
            'bar' => ['baz']
        ], $array);
    }

    function test_unsetting_array_element()
    {
        $array = [
            'foo' => 'bar',
            'baz' => ['qux']
        ];
        Arrays::unset($array, 'foo');
        Arrays::unset($array, ['baz', 0]);
        $this->assertSame(['baz' => []], $array);
    }

    function test_cannot_unset_from_non_array_element()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Traversing the array at path ["foo","bar"] contains non-array element.');
        $array = ['foo' => 'bar'];
        Arrays::unset($array, ['foo', 'bar']);
    }
}
