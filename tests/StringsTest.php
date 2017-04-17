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

    function test_string_starts_with_given_strings()
    {
        $this->assertTrue(Strings::startsWith('lorem ipsum', ['lorem', 'ipsum']));
        $this->assertTrue(Strings::startsWith('lorem ipsum', ['ipsum', 'lorem']));
        $this->assertFalse(Strings::startsWith('lorem ipsum', ['Lorem', 'Ipsum']));
    }

    function test_string_ends_with_given_string()
    {
        $this->assertTrue(Strings::endsWith('lorem ipsum', 'ipsum'));
        $this->assertTrue(Strings::endsWith('lorem ipsum', ''));
        $this->assertFalse(Strings::endsWith('lorem ipsum', 'Ipsum'));
        $this->assertFalse(Strings::endsWith('lorem ipsum', 'lorem ipsum '));
    }

    function test_string_ends_with_given_strings()
    {
        $this->assertTrue(Strings::startsWith('lorem ipsum', ['lorem', 'ipsum']));
        $this->assertTrue(Strings::startsWith('lorem ipsum', ['ipsum', 'lorem']));
        $this->assertFalse(Strings::startsWith('lorem ipsum', ['Lorem', 'Ipsum']));
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

    function test_string_contains_given_strings()
    {
        $this->assertTrue(Strings::contains('lorem ipsum', ['lorem', 'ipsum']));
        $this->assertTrue(Strings::contains('lorem ipsum', ['ipsum', 'lorem']));
        $this->assertTrue(Strings::contains('lorem ipsum', ['rem']));
        $this->assertFalse(Strings::contains('lorem ipsum', ['Lorem', 'Ipsum']));
    }

    function test_converting_string_to_ascii()
    {
        $this->assertSame('Internationalizaetion', Strings::toAscii('Iñtërnâtiônàlizætiøn'));
        $this->assertSame(
            'Prilis ZLUTOUCKY KUN upel dabelske ody',
            Strings::toAscii('Příliš ŽLUŤOUČKÝ KŮŇ úpěl ďábelské ódy')
        );
        $this->assertSame('Tarikh', Strings::toAscii('Taʾrikh'));
        $this->assertSame('Z `\'"^~?', Strings::toAscii("\xc5\xbd `'\"^~?"));
        $this->assertSame('"""\'\'\'>><<^', Strings::toAscii('„“”‚‘’»«°'));
        $this->assertSame('', Strings::toAscii("\xF0\x90\x80\x80")); // U+10000
        $this->assertSame('', Strings::toAscii("\xC2\xA4")); // non-ASCII character
        $this->assertSame('a b', Strings::toAscii("a\xC2\xA0b")); // non-breaking space

        if (class_exists('Transliterator') && \Transliterator::create('Any-Latin; Latin-ASCII')) {
            $this->assertSame('Athena->Moskva', Strings::toAscii('Αθήνα→Москва'));
        }
    }

    function test_converting_string_to_web_safe_characters()
    {
        $this->assertSame('internationalizaetion', Strings::slugify('&Iñtërnâtiônàlizætiøn!'));
        $this->assertSame(
            'Prilis-ZLUTOUCKY-KUN-upel-dabelske-ody',
            Strings::slugify('&Příliš ŽLUŤOUČKÝ KŮŇ úpěl ďábelské ódy!', null, false)
        );
        $this->assertSame('1-4-!', Strings::slugify("\xc2\xBC !", '!')); // non-ASCII character
        $this->assertSame('a-b', Strings::slugify("a\xC2\xA0b")); // non-breaking space
    }

    function test_slugify_uses_cache()
    {
        $class = get_class(new class extends Strings {
            /** @var int */
            public static $toAsciiCalled = 0;

            public static function toAscii(string $string): string
            {
                ++self::$toAsciiCalled;
                return parent::toAscii($string);
            }
        });

        $class::slugify('test');
        $class::slugify('test');

        $this->assertSame(1, $class::$toAsciiCalled);
    }
}
