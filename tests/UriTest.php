<?php

namespace Vanio\Stdlib\Tests;

use PHPUnit\Framework\TestCase;
use Vanio\Stdlib\Uri;

class UriTest extends TestCase
{
    function test_parsing_uri_parts()
    {
        $uri = new Uri('https://user:password@example.com:8080/path/123?foo=bar#fragment');

        $this->assertSame('https', $uri->scheme());
        $this->assertSame('user', $uri->user());
        $this->assertSame('password', $uri->password());
        $this->assertSame('user:password', $uri->userInfo());
        $this->assertSame('example.com', $uri->host());
        $this->assertSame(8080, $uri->port());
        $this->assertSame('user:password@example.com:8080', $uri->authority());
        $this->assertSame('/path/123', $uri->path());
        $this->assertSame('foo=bar', $uri->query());
        $this->assertSame(['foo' => 'bar'], $uri->queryParameters());
        $this->assertSame('bar', $uri->getQueryParameter('foo'));
        $this->assertSame('fragment', $uri->fragment());
        $this->assertSame('https://user:password@example.com:8080/path/123?foo=bar#fragment', $uri->absoluteUri());
        $this->assertSame('https://user:password@example.com:8080', $uri->hostUri());
        $this->assertSame('https://user:password@example.com:8080/path/123?foo=bar#fragment', (string) $uri);
    }

    function test_query_is_properly_encoded()
    {
        $this->assertSame('foo=/bar', (new Uri('?foo=/bar'))->query());
        $this->assertSame('foo=/%24bar', (new Uri('?foo=/$bar'))->query());
    }

    function test_parsing_invalid_uri()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Malformed or unsupported URI "//".');
        new Uri('//');
    }

    function test_cloning_uri()
    {
        $uri = new Uri('https://user:password@example.com:8080/path/123?foo=bar#fragment');
        $clonedUri = new Uri($uri);
        $this->assertSame($uri->absoluteUri(), $clonedUri->absoluteUri());
    }

    function test_changing_uri_parts()
    {
        $uri = (new Uri)
            ->withScheme('https')
            ->withUserInfo('user', 'password')
            ->withHost('example.com')
            ->withPort(8080)
            ->withPath('/path/123')
            ->withQuery('foo=bar')
            ->withFragment('fragment');

        $this->assertSame('https', $uri->scheme());
        $this->assertSame('user:password@example.com:8080', $uri->authority());
        $this->assertSame('/path/123', $uri->path());
        $this->assertSame('foo=bar', $uri->query());
        $this->assertSame('fragment', $uri->fragment());
        $this->assertSame('https://user:password@example.com:8080/path/123?foo=bar#fragment', (string) $uri);
    }

    function test_empty_optional_parts_are_null()
    {
        $uri = (new Uri)
            ->withScheme('')
            ->withUserInfo('', '')
            ->withHost('')
            ->withPort(null)
            ->withPath('')
            ->withQuery('')
            ->withFragment('');

        $this->assertNull($uri->scheme());
        $this->assertNull($uri->user());
        $this->assertNull($uri->password());
        $this->assertNull($uri->userInfo());
        $this->assertNull($uri->host());
        $this->assertNull($uri->port());
        $this->assertNull($uri->authority());
        $this->assertNull($uri->fragment());
    }

    function test_calling_immutable_setters_preserve_instance_when_passing_unchanged_value()
    {
        $uri = new Uri('https://user:password@example.com:8080/path/123?foo=bar#fragment');
        $unchanchedUri = $uri
            ->withScheme('https')
            ->withUserInfo('user', 'password')
            ->withHost('example.com')
            ->withPort(8080)
            ->withPath('/path/123')
            ->withQuery('foo=bar')
            ->withFragment('fragment');

        $this->assertSame($uri, $unchanchedUri);
    }

    function test_appending_query()
    {
        $uri = new Uri('?foo=bar');

        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $uri->withAppendedQuery('baz=qux')->queryParameters());
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $uri->withAppendedQuery(['baz' => 'qux'])->queryParameters());
        $this->assertEquals(['foo' => 'foo'], $uri->withAppendedQuery('foo=foo')->queryParameters());
    }

    function test_obtaining_absolute_uri()
    {
        $absoluteUri = 'https://user:password@example.com:8080/path/123?foo=bar#fragment';
        $this->assertSame($absoluteUri, (new Uri($absoluteUri))->absoluteUri());
        $this->assertSame('', (new Uri)->absoluteUri());
        $this->assertSame('?foo=bar', (new Uri)->withAppendedQuery(['foo' => 'bar'])->absoluteUri());
    }

    function test_uri_equals_to_another_uri()
    {
        $uri = new Uri('http://exampl%65.COM/p%61th?text=foo%20bar+baz&value');
        $this->assertTrue($uri->equals('http://example.com/path?text=foo+bar%20baz&value'));
        $this->assertTrue($uri->equals('http://example.com/%70ath?value&text=foo+bar%20baz'));

        $uri = new Uri('http://example.com');
        $this->assertTrue($uri->equals('http://example.com/'));
        $this->assertTrue($uri->equals('http://example.com'));

        $uri = new Uri('http://example.com/?array[]=foo&array[]=bar');
        $this->assertTrue($uri->equals('http://example.com/?array[0]=foo&array[1]=bar'));

        $uri = new Uri('http://example.com/?foo=9999&bar=127.0.0.1&baz=1234&qux=123456789');
        $this->assertTrue($uri->equals('http://example.com/?qux=123456789&foo=9999&bar=127.0.0.1&baz=1234'));

        $uri = (new Uri)->withScheme('http')->withHost('example.com');
        $this->assertTrue($uri->equals('http://example.com'));
    }

    function test_uri_does_not_equal_to_another_uri()
    {
        $uri = new Uri('http://example.com/path?text=foo+bar+baz&value');
        $this->assertFalse($uri->equals('http://example.com/Path?text=foo+bar%20baz&value'));
        $this->assertFalse($uri->equals('http://example.com/path?value&text=foo+bar%20baz#abc'));
        $this->assertFalse($uri->equals('http://example.com/path?text=foo+bar%20baz'));
        $this->assertFalse($uri->equals('https://example.com/path?text=foo+bar%20baz&value'));
        $this->assertFalse($uri->equals('http://example.org/path?text=foo+bar%20baz&value'));

        $uri = new Uri('http://example.com/?array[]=foo&array[]=bar');
        $this->assertFalse($uri->equals('http://example.com/?array[1]=foo&array[0]=bar'));

        $uri = new Uri('http://example.com/?foo=123&bar=456');
        $this->assertFalse($uri->equals('http://example.com/?foo=456&bar=123'));

        $uri = new Uri('http://user:password@example.com');
        $this->assertFalse($uri->equals('http://example.com'));
    }

    function test_parsing_query()
    {
        $this->assertSame([], Uri::parseQuery(''));
        $this->assertSame(['key' => ''], Uri::parseQuery('key'));
        $this->assertSame(['key' => ''], Uri::parseQuery('key='));
        $this->assertSame(['key' => 'value'], Uri::parseQuery('key=value'));
        $this->assertSame(['key' => ''], Uri::parseQuery('&key=&'));
        $this->assertSame(['foo' => ['bar', 'bar']], Uri::parseQuery('foo[]=bar&foo[]=bar'));
        $this->assertSame(['a' => ['x' => 'value', 'y' => 'value']], Uri::parseQuery('%61[x]=value&%61[y]=value'));
        $this->assertSame(['a_b' => 'value', 'c' => ['d e' => 'value']], Uri::parseQuery('a b=value&c[d e]=value'));
        $this->assertSame(['a_b' => 'value', 'c' => ['d.e' => 'value']], Uri::parseQuery('a.b=value&c[d.e]=value'));
        $this->assertSame(['key"\'' => '"\''], Uri::parseQuery('key"\'="\'')); // magic quotes
    }

    function test_encoding_query()
    {
        $this->assertSame('', Uri::encodeQuery([]));
        $this->assertSame('key=', Uri::encodeQuery(['key' => '']));
        $this->assertSame('key=value', Uri::encodeQuery(['key' => 'value']));
        $this->assertSame('a%5Bx%5D=value&a%5By%5D=value', Uri::encodeQuery(['a' => ['x' => 'value', 'y' => 'value']]));
        $this->assertSame('foo%5B%5D=bar&foo%5B%5D=bar', Uri::encodeQuery(['foo' => ['bar', 'bar']]));
        $this->assertSame('foo%5B1%5D=bar&foo%5B2%5D=bar', Uri::encodeQuery(['foo' => [1 => 'bar', 2 => 'bar']]));
        $this->assertSame('1=foo&2=bar', Uri::encodeQuery([1 => 'foo', 2 => 'bar']));
    }
}
