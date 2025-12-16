<?php

namespace Vanio\Stdlib;

abstract class Strings
{
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
    public static function endsWith(string $string, $values): bool
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
    public static function contains(string $string, $values): bool
    {
        foreach ((array) $values as $value) {
            if (strpos($string, $value) !== false) {
                return true;
            }
        }

        return false;
    }

    public static function substring(string $string, int $start, ?int $length = null): string
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    public static function upper(string $string): string
    {
        return mb_strtoupper($string, 'UTF-8');
    }

    public static function lower(string $string): string
    {
        return mb_strtolower($string, 'UTF-8');
    }

    public static function capitalize(string $string): string
    {
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }

    public static function upperFirst(string $string): string
    {
        return self::upper(self::substring($string, 0, 1)) . self::substring($string, 1);
    }

    public static function lowerFirst(string $string): string
    {
        return self::lower(self::substring($string, 0, 1)) . self::substring($string, 1);
    }

    public static function trim(string $string, ?string $characters = null): string
    {
        $characters = $characters === null ? '\s\0' : preg_quote($characters, '~');

        return preg_replace("~^[$characters]+|[$characters]+\z~u", '', $string);
    }

    public static function trimLeft(string $string, ?string $characters = null): string
    {
        $characters = $characters === null ? '\s\0' : preg_quote($characters, '~');

        return preg_replace("~^[$characters]+~u", '', $string);
    }

    public static function trimRight(string $string, ?string $characters = null): string
    {
        $characters = $characters === null ? '\s\0' : preg_quote($characters, '~');

        return preg_replace("~[$characters]+\z~u", '', $string);
    }

    public static function baseName(string $string): string
    {
        if (DIRECTORY_SEPARATOR === '/') {
            $string = strtr($string, '\\', '/');
        }

        return basename($string);
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
        $string = strtr($string, '`\'"^~?', "\1\2\3\4\5\6");
        $string = str_replace(
            ["\u{201E}", "\u{201C}", "\u{201D}", "\u{201A}", "\u{2018}", "\u{2019}", "\u{B0}"],
            ["\3", "\3", "\3", "\2", "\2", "\2", "\4"],
            $string
        );

        if ($transliterator) {
            $string = $transliterator->transliterate($string);
        }

        if (ICONV_IMPL === 'glibc') {
            $string = str_replace(
                ["\u{BB}", "\u{AB}", "\u{2026}", "\u{2122}", "\u{A9}", "\u{AE}"],
                ['>>', '<<', '...', 'TM', '(c)', '(R)'],
                $string
            );
            $string = iconv('UTF-8', 'WINDOWS-1250//TRANSLIT//IGNORE', $string);
            $string = strtr(
                $string,
                "\xA5\xA3\xBC\x8C\xA7\x8A\xAA\x8D\x8F\x8E\xAF\xB9\xB3\xBE\x9C\x9A\xBA\x9D\x9F\x9E\xBF\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xDB\xDC\xDD\xDE\xDF\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF0\xF1\xF2\xF3\xF4\xF5\xF6\xF8\xF9\xFA\xFB\xFC\xFD\xFE\x96\xA0\x8B\x97\x9B\xA6\xAD\xB7",
                'ALLSSSSTZZZallssstzzzRAAAALCCCEEEEIIDDNNOOOOxRUUUUYTsraaaalccceeeeiiddnnooooruuuuyt- <->|-.'
            );
            $string = preg_replace('~[^\x00-\x7F]++~', '', $string);
        } else {
            $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        }

        $string = str_replace(['`', "'", '"', '^', '~', '?'], '', $string);

        return strtr($string, "\1\2\3\4\5\6", '`\'"^~?');
    }

    /**
     * Convert the given $string to web safe characters [a-z0-9-].
     *
     * @param string $string
     * @param string|null $additionalCharacter Additional allowed characters
     * @param bool $shouldLower Whether to convert the resulting string to lowercase or not
     * @return string
     */
    public static function slugify(string $string, ?string $additionalCharacter = null, bool $shouldLower = true): string
    {
        static $slugs = [];

        if ($slug = $slugs[$string][$additionalCharacter][$shouldLower] ?? null) {
            return $slug;
        }

        $slug = static::toAscii($string);

        if ($shouldLower) {
            $slug = strtolower($slug);
        }

        $slug = preg_replace(
            sprintf('~[^a-z0-9%s]+~i', $additionalCharacter !== null ? preg_quote($additionalCharacter, '~') : ''),
            '-',
            $slug
        );
        $slug = trim($slug, '-');
        $slugs[$string][$additionalCharacter][$shouldLower] = $slug;

        return $slug;
    }

    /**
     * @return string[]
     */
    public static function matchWords(string $string): array
    {
        preg_match_all('~\p{Lu}?\p{Ll}+|\p{Lu}+(?=\p{Lu}\p{Ll})|\p{Lu}+|\p{N}+~u', $string, $matches);

        return $matches[0];
    }

    public static function convertToPascalCase(string $string): string
    {
        $words = self::matchWords($string);

        return implode('', array_map([self::class, 'capitalize'], $words));
    }

    public static function convertToCamelCase(string $string): string
    {
        return self::lowerFirst(self::convertToPascalCase($string));
    }

    public static function convertToSnakeCase(string $string): string
    {
        return self::lower(implode('_', self::matchWords($string)));
    }
}
