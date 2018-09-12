<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router;

use Cocur\Slugify\Slugify;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Spiral\Router\Traits\DefaultsTrait;
use Spiral\Router\Traits\VerbsTrait;

abstract class AbstractRoute implements RouteInterface
{
    use VerbsTrait, DefaultsTrait;

    /** @var UriHandler */
    protected $handler;

    /** @var array|null */
    protected $matches = null;

    /**
     * @param string $pattern
     * @param array  $defaults
     */
    public function __construct(string $pattern, array $defaults = [])
    {
        $this->handler = new UriHandler($pattern, new Slugify());
        $this->defaults = $defaults;
    }

    /**
     * @inheritdoc
     */
    public function withPrefix(string $prefix): RouteInterface
    {
        $route = clone $this;
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getMatches(): ?array
    {
        return $this->matches;
    }

    /**
     * @inheritdoc
     */
    public function uri($parameters = []): UriInterface
    {
        return $this->handler->uri(
            $parameters,
            array_merge($this->defaults, $this->matches ?? [])
        );
    }

    /**
     * Clones underlying Uri handler.
     */
    public function __clone()
    {
        $this->handler = clone $this->handler;
    }
}