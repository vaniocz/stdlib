<?php

namespace Vanio\Stdlib;

class Strings
{
    /**
     * Prevent instantiating.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {}

    /**
     * Find out whether the given $string starts with any of the given $values.
     *
     * @param string $string
     * @param string|string[] $values
     * @return bool
     */
    public static function startsWith(string $string, $values): bool
    {
        foreach ((array) $values as $value) {
            if (!strncmp($string, $value, strlen($value))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find out whether the given $string ends with any of the given $values.
     *
     * @param string $string
     * @param string|string[] $values
     * @return bool
     */
    public static function endsWith(string $string, $values)
    {
        foreach ((array) $values as $value) {
            if ($value === '' || substr($string, -strlen($value)) === $value) {
                return true;
            }
        }

        return false;
    }


    /**
     * Find out whether the given $string contains any of the given $values.
     *
     * @param string $string
     * @param string|string[] $values
     * @return bool
     */
    public static function contains($string, $values): bool
    {
        foreach ((array) $values as $value) {
            if (strpos($string, $value) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert the given $string to ASCII.
     *
     * @param string $string
     * @return string
     */
    public static function toAscii(string $string): string
    {
        static $transliterator;

        if (!$transliterator && class_exists('Transliterator', false)) {
            $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
        }

        $string = preg_replace('~[^\x09\x0A\x0D\x20-\x7E\xA0-\x{2FF}\x{370}-\x{10FFFF}]~u', '', $string);
        $string = strtr($string, '`\'"^~?', "\x01\x02\x03\x04\x05\x06");
        $string = str_replace(
            ["\xE2\x80\x9E", "\xE2\x80\x9C", "\xE2\x80\x9D", "\xE2\x80\x9A", "\xE2\x80\x98", "\xE2\x80\x99", "\xC2\xB0"],
            ["\x03", "\x03", "\x03", "\x02", "\x02", "\x02", "\x04"],
            $string
        );

        if ($transliterator) {
            $string = $transliterator->transliterate($string);
        }

        if (ICONV_IMPL === 'glibc') {
            $string = str_replace(
                ["\xC2\xBB", "\xC2\xAB", "\xE2\x80\xA6", "\xE2\x84\xA2", "\xC2\xA9", "\xC2\xAE"],
                ['>>', '<<', '...', 'TM', '(c)', '(R)'],
                $string
            );
            $string = iconv('UTF-8', 'WINDOWS-1250//TRANSLIT//IGNORE', $string);
            $string = strtr(
                $string,
                "\xa5\xa3\xbc\x8c\xa7\x8a\xaa\x8d\x8f\x8e\xaf\xb9\xb3\xbe\x9c\x9a\xba\x9d\x9f\x9e\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf8\xf9\xfa\xfb\xfc\xfd\xfe\x96\xa0\x8b\x97\x9b\xa6\xad\xb7",
                'ALLSSSSTZZZallssstzzzRAAAALCCCEEEEIIDDNNOOOOxRUUUUYTsraaaalccceeeeiiddnnooooruuuuyt- <->|-.'
            );
            $string = preg_replace('~[^\x00-\x7F]++~', '', $string);
        } else {
            $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        }

        $string = str_replace(['`', "'", '"', '^', '~', '?'], '', $string);

        return strtr($string, "\x01\x02\x03\x04\x05\x06", '`\'"^~?');
    }

    /**
     * Convert the given $string to web safe characters [a-z0-9-].
     *
     * @param string $string
     * @param string $additionalCharacter Additional allowed characters
     * @param bool $lowercase Whether to lowercase or not
     * @return string
     */
    public static function slugify(string $string, string $additionalCharacter = null, bool $lowercase = true): string
    {
        $string = self::toAscii($string);

        if ($lowercase) {
            $string = strtolower($string);
        }

        $string = preg_replace(
            sprintf('~[^a-z0-9%s]+~i', $additionalCharacter !== null ? preg_quote($additionalCharacter, '~') : ''),
            '-',
            $string
        );

        return trim($string, '-');
    }
}
