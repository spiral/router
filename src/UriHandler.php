<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing;

use Cocur\Slugify\SlugifyInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Http\Uri;

/**
 * UriMatcher provides ability to match and generate uris based on given parameters.
 */
class UriHandler
{
    const DEFAULT_SEGMENT = '[^\/]+';
    const REPLACES = ['/' => '\\/', '[' => '(?:', ']' => ')?', '.' => '\.'];
    const URI_FIXERS = ['[]' => '', '[/]' => '', '[' => '', ']' => '', '://' => '://', '//' => '/'];

    /** @var string */
    private $pattern;

    /** @var SlugifyInterface */
    private $slugify;

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
     */
    public function __construct(string $pattern, SlugifyInterface $slugify)
    {
        $this->pattern = $pattern;
        $this->slugify = $slugify;
    }

    /**
     * @param string $pattern
     */
    public function setPattern(string $pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @param bool $matchHost
     */
    public function setMatchHost(bool $matchHost)
    {
        $this->matchHost = $matchHost;
    }

    /**
     * @return bool
     */
    public function isMatchHost(): bool
    {
        return $this->matchHost;
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
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return bool
     */
    public function isCompiled(): bool
    {
        return !empty($this->compiled);
    }

    /**
     * Wipe compiled values on clone.
     */
    public function __clone()
    {
        $this->compiled = null;
        $this->template = null;
        $this->options = [];
    }

    /**
     * Match given url against compiled template and return matches array or null if pattern does not match.
     *
     * @param UriInterface $uri
     * @param array        $defaults
     * @return array|null
     */
    public function match(UriInterface $uri, array $defaults): ?array
    {
        if (!$this->isCompiled()) {
            $this->compile();
        }

        $matches = [];
        if (!preg_match($this->pattern, $this->fetchTarget($uri), $matches)) {
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
            //This segment fetched keys from given parameters either by name or by position
            if (is_numeric($key) && isset($allowed[$key])) {
                $key = $allowed[$key];
            } elseif (!array_key_exists($key, $this->options) && is_array($parameters)) {
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
        $options = [];
        if (preg_match_all('/<(\w+):?(.*?)?>/', $this->pattern, $matches)) {
            $variables = array_combine($matches[1], $matches[2]);

            foreach ($variables as $name => $segment) {
                //Segment regex
                $segment = $segment ?? self::DEFAULT_SEGMENT;
                $replaces["<$name>"] = "(?P<$name>$segment)";
                $options[] = $name;
            }
        }

        $template = preg_replace('/<(\w+):?.*?>/', '<\1>', $this->pattern);

        $this->pattern = '/^' . strtr($template, self::REPLACES) . '$/iu';
        $this->template = stripslashes(str_replace('?', '', $template));
        $this->options = array_fill_keys($options, null);
    }

    /**
     * Interpolate string with given values.
     *
     * @param string $string
     * @param array  $values
     * @return string
     */
    private function interpolate(string $string, array $values): string
    {
        $replaces = [];
        foreach ($values as $key => $value) {
            $value = (is_array($value) || $value instanceof \Closure) ? '' : $value;

            try {
                //Object as string
                $value = is_object($value) ? (string)$value : $value;
            } catch (\Exception $e) {
                $value = '';
            }

            $replaces["<{$key}>"] = $value;
        }

        return strtr($string, $replaces + self::URI_FIXERS);
    }
}