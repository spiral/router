<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router;

use Cocur\Slugify\SlugifyInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Http\Uri;
use Spiral\Router\Exceptions\ConstrainException;
use Spiral\Router\Exceptions\UriHandlerException;

/**
 * UriMatcher provides ability to match and generate uris based on given parameters.
 */
class UriHandler
{
    private const HOST_PREFIX = '//';
    private const DEFAULT_SEGMENT = '[^\/]+';
    private const PATTERN_REPLACES = ['/' => '\\/', '[' => '(?:', ']' => ')?', '.' => '\.'];
    private const SEGMENT_REPLACES = ['/' => '\\/', '.' => '\.'];
    private const URI_FIXERS = [
        '[]'  => '',
        '[/]' => '',
        '['   => '',
        ']'   => '',
        '://' => '://',
        '//'  => '/'
    ];

    /** @var string */
    private $pattern;

    /**
     * @invisible
     * @var SlugifyInterface
     */
    private $slugify;

    /** @var array */
    private $constrains = [];

    /** @var bool */
    private $matchHost = false;

    /** @var string */
    private $prefix = '';

    /** @var string|null */
    private $compiled;

    /** @var string|null */
    private $template;

    /** @var array */
    private $options = [];

    /**
     * @param string           $pattern
     * @param SlugifyInterface $slugify
     * @param array            $constrains
     */
    public function __construct(string $pattern, SlugifyInterface $slugify, array $constrains = [])
    {
        $this->matchHost = strpos($pattern, self::HOST_PREFIX) === 0;
        $this->pattern = $pattern;
        $this->slugify = $slugify;
        $this->constrains = $constrains;
    }

    /**
     * @return array
     */
    public function getConstrains(): array
    {
        return $this->constrains;
    }

    /**
     * @param mixed $prefix
     */
    public function setPrefix($prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return bool
     */
    public function isCompiled(): bool
    {
        return !empty($this->compiled);
    }

    /**
     * Match given url against compiled template and return matches array or null if pattern does
     * not match.
     *
     * @param UriInterface $uri
     * @param array        $defaults
     *
     * @return array|null
     */
    public function match(UriInterface $uri, array $defaults): ?array
    {
        if (!$this->isCompiled()) {
            $this->compile();
        }

        $matches = [];
        if (!preg_match($this->compiled, $this->fetchTarget($uri), $matches)) {
            return null;
        }

        $matches = array_intersect_key($matches, $this->options);

        return array_merge($this->options, $defaults, $matches);
    }

    /**
     * Generate Uri for a given parameters and default values.
     *
     * @param array|\Traversable $parameters
     * @param array              $defaults
     *
     * @return UriInterface
     */
    public function uri($parameters = [], array $defaults = []): UriInterface
    {
        if (!$this->isCompiled()) {
            $this->compile();
        }

        $parameters = array_merge(
            $this->options,
            $defaults,
            $this->fetchOptions($parameters, $query)
        );

        foreach ($this->constrains as $key => $values) {
            if (empty($parameters[$key])) {
                throw new UriHandlerException("Unable to generate Uri, parameter `{$key}` is missing.");
            }
        }

        //Uri without empty blocks (pretty stupid implementation)
        $path = $this->interpolate($this->template, $parameters);

        //Uri with added prefix
        $uri = new Uri(($this->matchHost ? '' : $this->prefix) . trim($path, '/'));

        return empty($query) ? $uri : $uri->withQuery(http_build_query($query));
    }

    /**
     * Fetch uri segments and query parameters.
     *
     * @param \Traversable|array $parameters
     * @param array|null         $query Query parameters.
     *
     * @return array
     */
    private function fetchOptions($parameters, &$query): array
    {
        $allowed = array_keys($this->options);

        $result = [];
        foreach ($parameters as $key => $parameter) {
            if (is_numeric($key) && isset($allowed[$key])) {
                // this segment fetched keys from given parameters either by name or by position
                $key = $allowed[$key];
            } elseif (!array_key_exists($key, $this->options) && is_array($parameters)) {
                // all additional parameters given in array form can be glued to query string
                $query[$key] = $parameter;
                continue;
            }

            //String must be normalized here
            if (is_string($parameter) && !preg_match('/^[a-z\-_0-9]+$/i', $parameter)) {
                $result[$key] = $this->slugify->slugify($parameter);
                continue;
            }

            $result[$key] = (string)$parameter;
        }

        return $result;
    }

    /**
     * Part of uri path which is being matched.
     *
     * @param UriInterface $uri
     *
     * @return string
     */
    private function fetchTarget(UriInterface $uri): string
    {
        $path = $uri->getPath();

        if (empty($path) || $path[0] !== '/') {
            $path = '/' . $path;
        }

        if ($this->matchHost) {
            $uri = $uri->getHost() . $path;
        } else {
            $uri = substr($path, strlen($this->prefix));
        }

        return trim($uri, '/');
    }

    /**
     * Compile route matcher into regexp.
     */
    private function compile()
    {
        $options = $replaces = [];
        $pattern = rtrim(ltrim($this->pattern, ':/'), '/');

        if (preg_match_all('/<(\w+):?(.*?)?>/', $pattern, $matches)) {
            $variables = array_combine($matches[1], $matches[2]);

            foreach ($variables as $key => $segment) {
                $segment = $this->prepareSegment($key, $segment);
                $replaces["<$key>"] = "(?P<$key>$segment)";
                $options[] = $key;
            }
        }

        $template = preg_replace('/<(\w+):?.*?>/', '<\1>', $pattern);
        $options = array_fill_keys($options, null);

        foreach ($this->constrains as $key => $value) {
            if ($value instanceof Autofill) {
                // only forces value replacement, not required to be presented as parameter
                continue;
            }

            if (!array_key_exists($key, $options)) {
                throw new ConstrainException(sprintf(
                    "Route `%s` does not define routing parameter `<%s>`.",
                    $this->pattern,
                    $key
                ));
            }
        }

        $this->compiled = '/^' . strtr($template, $replaces + self::PATTERN_REPLACES) . '$/iu';
        $this->template = stripslashes(str_replace('?', '', $template));
        $this->options = $options;
    }

    /**
     * Interpolate string with given values.
     *
     * @param string $string
     * @param array  $values
     *
     * @return string
     */
    private function interpolate(string $string, array $values): string
    {
        $replaces = [];
        foreach ($values as $key => $value) {
            $value = (is_array($value) || $value instanceof \Closure) ? '' : $value;
            $replaces["<{$key}>"] = is_object($value) ? (string)$value : $value;
        }

        return strtr($string, $replaces + self::URI_FIXERS);
    }

    /**
     * Prepares segment pattern with given constrains.
     *
     * @param string $name
     * @param string $segment
     *
     * @return string
     */
    private function prepareSegment(string $name, string $segment): string
    {
        if (!empty($segment)) {
            return $this->filterSegment($segment);
        }

        if (!isset($this->constrains[$name])) {
            return self::DEFAULT_SEGMENT;
        }

        if (is_array($this->constrains[$name])) {
            $values = array_map([$this, 'filterSegment'], $this->constrains[$name]);

            return join('|', $values);
        }

        return $this->filterSegment($this->constrains[$name]);
    }

    /**
     * @param string $segment
     *
     * @return string
     */
    private function filterSegment(string $segment): string
    {
        return strtr($segment, self::SEGMENT_REPLACES);
    }
}