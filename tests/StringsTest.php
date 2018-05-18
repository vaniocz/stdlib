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
        $this->assertTrue(Strings::startsWith('Iñtërnâtiônàlizætiøn', 'Iñtër'));
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
        $this->assertTrue(Strings::endsWith('Iñtërnâtiônàlizætiøn', 'nâtiônàlizætiøn'));
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
        $this->assertTrue(Strings::contains('Iñtërnâtiônàlizætiøn', 'nâtiôn'));
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

    function test_substring()
    {
        $this->assertSame('lorem', Strings::substring('lorem ipsum', 0, 5));
        $this->assertSame('ipsum', Strings::substring('lorem ipsum', 6));
        $this->assertSame('nâtiôn', Strings::substring('Iñtërnâtiônàlizætiøn', 5, 6));
    }

    function test_converting_string_to_uppercase()
    {
        $this->assertSame('LOREM IPSUM', Strings::upper('lorem ipsum'));
        $this->assertSame('LOREM IPSUM', Strings::upper('Lorem Ipsum'));
        $this->assertSame('IÑTËRNÂTIÔNÀLIZÆTIØN', Strings::upper('Iñtërnâtiônàlizætiøn'));
    }

    function test_converting_string_to_lowercase()
    {
        $this->assertSame('lorem ipsum', Strings::lower('LOREM IPSUM'));
        $this->assertSame('lorem ipsum', Strings::lower('Lorem Ipsum'));
        $this->assertSame('iñtërnâtiônàlizætiøn', Strings::lower('IÑTËRNÂTIÔNÀLIZÆTIØN'));
    }

    function test_converting_string_to_capital_case()
    {
        $this->assertSame('Lorem Ipsum', Strings::capitalize('lorem ipsum'));
        $this->assertSame('Lorem Ipsum', Strings::capitalize('LOREM IPSUM'));
        $this->assertSame('Příliš Žluťoučký Kůň', Strings::capitalize('Příliš ŽLUŤOUČKÝ kůň'));
    }

    function test_converting_first_letter_to_uppercase()
    {
        $this->assertSame('Lorem ipsum', Strings::upperFirst('lorem ipsum'));
        $this->assertSame('Lorem Ipsum', Strings::upperFirst('Lorem Ipsum'));
        $this->assertSame('Žluťoučký kůň', Strings::upperFirst('žluťoučký kůň'));
    }

    function test_converting_first_letter_to_lowercase()
    {
        $this->assertSame('lOREM IPSUM', Strings::lowerFirst('LOREM IPSUM'));
        $this->assertSame('lorem Ipsum', Strings::lowerFirst('Lorem Ipsum'));
        $this->assertSame('žLUŤOUČKÝ KŮŇ', Strings::lowerFirst('ŽLUŤOUČKÝ KŮŇ'));
    }

    function test_obtaining_path_or_class_base_name()
    {
        $this->assertSame('', Strings::baseName(''));
        $this->assertSame('foo', Strings::baseName('foo'));
        $this->assertSame('bar', Strings::baseName('foo/bar'));
        $this->assertSame('bar', Strings::baseName('foo\bar'));
        $this->assertSame('baz', Strings::baseName('foo\bar/baz'));
        $this->assertSame('baz', Strings::baseName('foo/bar\baz'));
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
                self::$toAsciiCalled++;

                return parent::toAscii($string);
            }
        });

        $class::{'slugify'}('foo');
        $class::{'slugify'}('foo');

        $this->assertSame(1, $class::$toAsciiCalled);
    }

    function test_converting_string_to_camel_case()
    {
        $this->assertSame('', Strings::convertToCamelCase(''));
        $this->assertSame('loremIpsum', Strings::convertToCamelCase('loremIpsum'));
        $this->assertSame('loremIpsum', Strings::convertToCamelCase('lorem ipsum'));
        $this->assertSame('loremIpsum', Strings::convertToCamelCase("lorem\nipsum"));
        $this->assertSame('loremIpsum', Strings::convertToCamelCase('lorem  IPSUM'));
        $this->assertSame('loremIpsum', Strings::convertToCamelCase('lorem_ipsum'));
        $this->assertSame('loremIpsum', Strings::convertToCamelCase('lorem__ipsum'));
        $this->assertSame('loremIpsum', Strings::convertToCamelCase('Lorem-Ipsum'));
        $this->assertSame('loremIpsumDolor', Strings::convertToCamelCase('loremIPSUMDolor'));
        $this->assertSame(
            'přílišŽluťoučkýKůňÚpěl42ĎábelskýchÓd42KrátZaSebou',
            Strings::convertToCamelCase('Příliš_ŽLUŤOUČKÝ_KŮŇ-úpěl  42ďábelských ód 42 krát za sebou')
        );
    }

    function test_converting_string_to_pascal_case()
    {
        $this->assertSame('', Strings::convertToCamelCase(''));
        $this->assertSame('LoremIpsum', Strings::convertToPascalCase('loremIpsum'));
        $this->assertSame('LoremIpsum', Strings::convertToPascalCase('lorem ipsum'));
        $this->assertSame('LoremIpsum', Strings::convertToPascalCase("lorem\nipsum"));
        $this->assertSame('LoremIpsum', Strings::convertToPascalCase('lorem  IPSUM'));
        $this->assertSame('LoremIpsum', Strings::convertToPascalCase('lorem_ipsum'));
        $this->assertSame('LoremIpsum', Strings::convertToPascalCase('lorem__ipsum'));
        $this->assertSame('LoremIpsum', Strings::convertToPascalCase('Lorem-Ipsum'));
        $this->assertSame('LoremIpsumDolor', Strings::convertToPascalCase('loremIPSUMDolor'));
        $this->assertSame(
            'PřílišŽluťoučkýKůňÚpěl42ĎábelskýchÓd42KrátZaSebou',
            Strings::convertToPascalCase('Příliš_ŽLUŤOUČKÝ_KŮŇ-úpěl  42ďábelských ód 42 krát za sebou')
        );
    }
}
