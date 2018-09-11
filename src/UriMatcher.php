<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing;

use Psr\Http\Message\UriInterface;

/**
 * Compile route declarations into proper regexp expressions.
 */
class UriMatcher
{
    /**
     * Default segment pattern, this patter can be applied to controller names, actions and etc.
     */
    const DEFAULT_SEGMENT = '[^\/]+';

    //todo: PUBLIC?

    /** @var bool */
    private $matchHost = false;

    /** @var string */
    private $prefix = '';

    /** @var string */
    private $pattern = '';

    /** @var string */
    private $template = '';

    /** @var array */
    private $options = [];

    /**
     * Compile uri matcher based on given template.
     * Examples:
     *  - userPanel/<action>
     *  - [<controller>[/<action>[/<id>]]]
     *  - domain.com[/<controller>[/<action>[/<id>]]]
     *
     * @param string $prefix
     * @param string $pattern   Route pattern.
     * @param bool   $matchHost Match hostname.
     * @return UriMatcher
     */
    public static function compile(string $prefix, string $pattern, bool $matchHost): UriMatcher
    {
        $replaces = ['/' => '\\/', '[' => '(?:', ']' => ')?', '.' => '\.'];

        $options = [];
        if (preg_match_all('/<(\w+):?(.*?)?>/', $pattern, $matches)) {
            $variables = array_combine($matches[1], $matches[2]);

            foreach ($variables as $name => $segment) {
                //Segment regex
                $segment = $segment ?? self::DEFAULT_SEGMENT;
                $replaces["<$name>"] = "(?P<$name>$segment)";
                $options[] = $name;
            }
        }

        $template = preg_replace('/<(\w+):?.*?>/', '<\1>', $pattern);

        $matcher = new UriMatcher();
        $matcher->prefix = $prefix;
        $matcher->matchHost = $matchHost;
        $matcher->pattern = '/^' . strtr($template, $replaces) . '$/iu';
        $matcher->template = stripslashes(str_replace('?', '', $template));
        $matcher->options = array_fill_keys($options, null);

        return $matcher;
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
        $matches = [];
        if (!preg_match($this->pattern, $uri, $matches)) {
            return null;
        }

        $matches = array_intersect_key($matches, $this->options);
        return array_merge($this->options, $defaults, $matches);
    }

    public function build(): UriInterface
    {
        $parameters = array_merge(
            $this->options,
            $this->defaults,
            $this->matches,
            $this->fetchSegments($parameters, $query)
        );

        //Uri without empty blocks (pretty stupid implementation)
        $path = strtr(
            \Spiral\interpolate($this->compiled['template'], $parameters, '<', '>'),
            ['[]' => '', '[/]' => '', '[' => '', ']' => '', '://' => '://', '//' => '/']
        );

        //Uri with added prefix
        $uri = new Uri(($this->matchHost ? '' : $this->prefix) . trim($path, '/'));

        return empty($query) ? $uri : $uri->withQuery(http_build_query($query));
    }

    /**
     * Part of uri path which is being matched.
     *
     * @param UriInterface $uri
     * @return string
     */
    private function getTarget(UriInterface $uri): string
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
}