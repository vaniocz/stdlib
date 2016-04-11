<?php

namespace Vanio\Stdlib\Tests;

use PHPUnit_Framework_TestCase;
use Vanio\Stdlib\LinkedHashSet;

class LinkedHashSetTest extends PHPUnit_Framework_TestCase
{
    /** @var LinkedHashSet */
    private $set;

    function setUp()
    {
        $this->set = new LinkedHashSet();
    }

    function test_set_is_empty_at_the_beginning()
    {
        $this->assertCount(0, $this->set);
    }

    function test_certain_value_can_be_added_only_once()
    {
        $this->assertTrue($this->set->add('test'));
        $this->assertFalse($this->set->add('test'));
        $this->assertCount(1, $this->set);
    }

    function test_presence_of_a_value_in_the_set_can_be_determined()
    {
        $this->set->add('test');

        $this->assertTrue($this->set->contains('test'));
        $this->assertFalse($this->set->contains('bogus'));
    }

    function test_certain_value_can_be_removed()
    {
        $this->set->add('test');

        $this->assertTrue($this->set->remove('test'));
        $this->assertFalse($this->set->contains('test'));
        $this->assertCount(0, $this->set);
        $this->assertFalse($this->set->remove('test'));
    }

    function test_all_the_set_values_can_be_retrieved()
    {
        $values = [0, 1, 1.01, 'foo', ['name' => 'John', 'surname' => 'Doe'], (object) ['data' => ['something']]];

        foreach ($values as $value) {
            $this->set->add($value);
        }

        $this->assertSame($values, $this->set->values());
    }

    function test_set_can_be_iterated_through()
    {
        $object = (object) ['data' => ['something']];
        $expected = [0, 1.01, 'foo', ['name' => 'John', 'surname' => 'Doe'], $object];

        foreach ($expected as $value) {
            $this->set->add($value);
        }

        $index = 0;
        foreach ($this->set as $value) {
            $this->assertSame($expected[$index++], $value);
        }
    }
}
