<?php

namespace Vanio\Stdlib;

/**
 * URI Syntax (RFC 3986)
 *
 * <pre>
 * scheme    userInfo       host   port
 *  |           |            |       |
 * ╱‾‾╲   ╱‾‾‾‾‾‾‾‾‾‾‾╲ ╱‾‾‾‾‾‾‾‾‾╲ ╱‾‾╲
 * http://user:password@example.com:8042/en/manual.php?name=value#fragment
 *        ╲____________________________╱╲____________╱ ╲________╱ ╲______╱
 *                     |                      |            |         |
 *                 authority                path         query    fragment
 * </pre>
 */
class Uri
{
    const DEFAULT_PORTS = [
        'http'  => 80,
        'https' => 443,
        'ftp' => 21,
        'gopher' => 70,
        'nntp' => 119,
        'news' => 119,
        'telnet' => 23,
        'tn3270' => 23,
        'imap' => 143,
        'pop' => 110,
        'ldap' => 389,
    ];

    /** @var string|null */
    private $scheme;

    /** @var string|null */
    private $user;

    /** @var string|null */
    private $password;

    /** @var string|null */
    private $host;

    /** @var int|null */
    private $port;

    /** @var string */
    private $path;

    /** @var array */
    private $queryParameters;

    /** @var string|null */
    private $fragment;

    /**
     * @param self|string $uri
     * @throws \InvalidArgumentException
     */
    public function __construct($uri = '')
    {
        if ($uri instanceof self) {
            foreach ($this as $key => $val) {
                $this->$key = $uri->$key;
            }
        } else {
            if (!$parts = @parse_url($uri)) {
                throw new \InvalidArgumentException(sprintf('Malformed or unsupported URI "%s".', $uri));
            }

            $this->scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : null;
            $this->port = $parts['port'] ?? null;
            $this->host = isset($parts['host']) ? rawurldecode($parts['host']) : null;
            $this->user = isset($parts['user']) ? rawurldecode($parts['user']) : null;
            $this->password = isset($parts['pass']) ? rawurldecode($parts['pass']) : null;
            $this->path = $this->resolvePath($this->host, $parts['path'] ?? '');
            $this->setQuery($parts['query'] ?? []);
            $this->fragment = isset($parts['fragment']) ? rawurldecode($parts['fragment']) : null;
        }
    }

    /**
     * @return string|null
     */
    public function scheme()
    {
        return $this->scheme;
    }

    public function withScheme(string $scheme = null): self
    {
        $scheme = strtolower($scheme) ?: null;

        if ($this->scheme === $scheme) {
            return $this;
        }

        $self = clone $this;
        $self->scheme = $scheme;

        return $self;
    }

    /**
     * @return string|null
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * @return string|null
     */
    public function password()
    {
        return $this->password;
    }

    /**
     * @return string|null
     */
    public function userInfo()
    {
        if ($this->user === null && $this->password === null) {
            return null;
        }

        return sprintf('%s:%s', rawurlencode($this->user), rawurlencode($this->password));
    }

    public function withUserInfo(string $user = null, string $password = null): self
    {
        $user = $user === '' ? null : $user;
        $password = $password === '' ? null : $password;

        if ($this->user === $user && $this->password === $password) {
            return $this;
        }

        $self = clone $this;
        $self->user = $user;
        $self->password = $password;

        return $self;
    }

    /**
     * @return string|null
     */
    public function host()
    {
        return $this->host;
    }

    public function withHost(string $host = null): self
    {
        $host = $host === '' ? null : $host;
        $path = $this->resolvePath($host, $this->path);

        if ($this->host === $host && $this->path === $path) {
            return $this;
        }

        $self = clone $this;
        $self->host = $host;
        $self->path = $path;

        return $self;
    }

    /**
     * @return int|null
     */
    public function port()
    {
        return $this->port ?? self::DEFAULT_PORTS[$this->scheme] ?? null;
    }

    public function withPort(int $port = null): self
    {
        if ($this->port === $port) {
            return $this;
        }

        $self = clone $this;
        $self->port = $port;

        return $self;
    }

    /**
     * @return string|null
     */
    public function authority()
    {
        if ($this->host === null) {
            return null;
        }

        $authority = $this->host;

        if ($this->port !== null && self::DEFAULT_PORTS[$this->scheme] ?? false !== $this->port) {
            $authority .= ':' . $this->port;
        }

        if ($this->user !== null || $this->password !== null) {
            $authority = sprintf('%s@%s', $this->userInfo(), $authority);
        }

        return $authority;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function withPath(string $path): self
    {
        $path = $this->resolvePath($this->host, $path);

        if ($this->path === $path) {
            return $this;
        }

        $self = clone $this;
        $self->path = $path;

        return $self;
    }

    public function query(): string
    {
        return http_build_query($this->queryParameters, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * @param string|array $query
     * @return self
     */
    public function withQuery($query): self
    {
        $queryParameters = is_array($query) ? $query : self::parseQuery($query);

        if ($this->queryParameters === $queryParameters) {
            return $this;
        }

        $self = clone $this;
        $self->queryParameters = $queryParameters;

        return $self;
    }

    /**
     * @param string|array $value
     * @return self
     */
    public function withAppendedQuery($value): self
    {
        $self = clone $this;
        $self->queryParameters = (is_array($value) ? $value : self::parseQuery($value)) + $this->queryParameters;

        return $self;
    }

    public function queryParameters(): array
    {
        return $this->queryParameters;
    }

    public function getQueryParameter(string $parameter, $default = null)
    {
        return $this->queryParameters[$parameter] ?? $default;
    }

    /**
     * @return string|null
     */
    public function fragment()
    {
        return $this->fragment;
    }

    public function withFragment(string $fragment = null): self
    {
        $fragment = $fragment === '' ? null : $fragment;

        if ($this->fragment === $fragment) {
            return $this;
        }

        $self = clone $this;
        $self->fragment = $fragment;

        return $self;
    }

    public function absoluteUri(): string
    {
        $query = $this->query();

        return $this->hostUri()
            . $this->path
            . ($query === '' ? '' : '?' . $query)
            . ($this->fragment === null ? '' : '#' . $this->fragment);
    }

    /**
     * @return string|null
     */
    public function hostUri()
    {
        return sprintf('%s//%s', $this->scheme ? $this->scheme . ':' : '', $this->authority());
    }

    /**
     * @param self|string $uri
     * @return bool
     */
    public function equals($uri): bool
    {
        $uri = new self($uri);
        $thisQueryParameters = $this->queryParameters;
        ksort($thisQueryParameters);
        $uriQueryParameters = $uri->queryParameters;
        ksort($uriQueryParameters);

        return $this->scheme === $uri->scheme
            && !strcasecmp($this->host, $uri->host)
            && $this->port() === $uri->port()
            && $this->user === $uri->user
            && $this->password === $uri->password
            && self::unescape($this->path, '%/') === self::unescape($uri->path, '%/')
            && $thisQueryParameters === $uriQueryParameters
            && $this->fragment === $uri->fragment;
    }

    public function __toString(): string
    {
        return $this->absoluteUri();
    }

    public static function parseQuery(string $queryString): array
    {
        parse_str($queryString, $query);

        return $query;
    }

    /**
     * Unescapes URI similarly to rawurldecode but preserves reserved chars encoded.
     *
     * @param string $uri
     * @param string $reservedCharacters
     * @return string
     */
    public static function unescape(string $uri, string $reservedCharacters = '%;/?:@&=+$,'): string
    {
        if ($reservedCharacters !== '') {
            $pattern = substr(chunk_split(bin2hex($reservedCharacters), 2, '|'), 0, -1);
            $uri = preg_replace_callback(
                "~%($pattern)~i",
                function ($match) {
                    return '%25' . strtoupper($match[1]);
                },
                $uri
            );
        }

        return rawurldecode($uri);
    }

    private function resolvePath(string $host = null, string $path): string
    {
        return $host !== null && !Strings::startsWith($path, '/') ? '/' . $path : $path;
    }

    /**
     * @param string|array $query
     */
    private function setQuery($query)
    {
        $this->queryParameters = is_array($query) ? $query : self::parseQuery($query);
    }
}
