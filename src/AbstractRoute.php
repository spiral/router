<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing;

use Cocur\Slugify\Slugify;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Spiral\Router\RouteInterface;
use Spiral\Routing\Exceptions\RouteException;
use Spiral\Routing\Traits\DefaultsTrait;
use Spiral\Routing\Traits\VerbsTrait;

abstract class AbstractRoute implements RouteInterface
{
    use VerbsTrait, DefaultsTrait;

    /** @var UriHandler */
    protected $handler;

    /** @var array|null */
    protected $matches;

    /**
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        $this->handler = new UriHandler($pattern, new Slugify());
    }

    /**
     * @inheritdoc
     */
    public function withPrefix(string $prefix): RouteInterface
    {
        $route = clone $this;
        $route->handler = clone $this->handler;
        $route->handler->setPrefix($prefix);

        return $route;
    }

    /**
     * @inheritdoc
     */
    public function getPrefix(): string
    {
        return $this->handler->getPrefix();
    }

    /**
     * Enable to disable host name matching.
     *
     * @param bool $matchHost
     * @return RouteInterface
     */
    public function withHost(bool $matchHost = true): RouteInterface
    {
        $route = clone $this;
        $route->handler = clone $this->handler;
        $route->handler->setMatchHost($matchHost);

        return $route;
    }

    /**
     * Indicates that route will be matching hostname.
     *
     * @return bool
     */
    public function isMatchHost(): bool
    {
        return $this->handler->isMatchHost();
    }

    /**
     * Match route against given request, must return matched route instance or return null if route does
     * not match.
     *
     * @param Request $request
     * @return RouteInterface|$this|null
     *
     * @throws RouteException
     */
    public function match(Request $request): ?RouteInterface
    {
        $matches = $this->handler->match($request->getUri(), $this->defaults);
        if ($matches === null) {
            return null;
        }

        $route = clone $this;
        $route->matches = $matches;

        return $route;
    }

    /**
     * Generate valid route URL using set of routing parameters.
     *
     * @param array|\Traversable $parameters
     * @return UriInterface
     *
     * @throws RouteException
     */
    public function uri($parameters = []): UriInterface
    {
        return $this->handler->uri($parameters, array_merge($this->defaults, $this->matches));
    }
}