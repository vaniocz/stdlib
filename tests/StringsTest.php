<?php

namespace Vanio\Stdlib\Tests;

use PHPUnit\Framework\TestCase;
use Vanio\Stdlib\Strings;

class StringsTest extends TestCase
{
    function test_string_starts_with_given_string()
    {
        $this->assertTrue(Strings::startsWith('lorem ipsum', 'lorem'));
        $this->assertTrue(Strings::startsWith('lorem ipsum', ''));
        $this->assertFalse(Strings::startsWith('lorem ipsum', 'Lorem'));
    }

    function test_string_ends_with_given_string()
    {
        $this->assertTrue(Strings::endsWith('lorem ipsum', 'ipsum'));
        $this->assertTrue(Strings::endsWith('lorem ipsum', ''));
        $this->assertFalse(Strings::endsWith('lorem ipsum', 'Ipsum'));
        $this->assertFalse(Strings::endsWith('lorem ipsum', 'lorem ipsum '));
    }

    function test_string_contains_given_string()
    {
        $this->assertTrue(Strings::contains('lorem ipsum', 'lorem'));
        $this->assertTrue(Strings::contains('lorem ipsum', 'ipsum'));
        $this->assertTrue(Strings::contains('lorem ipsum', 'rem'));
        $this->assertFalse(Strings::contains('lorem ipsum', 'foo'));
        $this->assertFalse(Strings::contains('lorem ipsum', 'bar'));
        $this->assertFalse(Strings::contains('lorem ipsum', ' lorem'));
    }
}
