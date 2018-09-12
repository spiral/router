<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing\Traits;

use Psr\Http\Server\MiddlewareInterface;
use Spiral\Router\RouteInterface;
use Spiral\Routing\Exceptions\RouteException;

trait MiddlewareTrait
{
    /** @var MiddlewareInterface|string */
    protected $middleware = [];

    /**
     * Associated middleware with route. New instance of route will be returned.
     *
     * Example:
     * $route->withMiddleware(new CacheMiddleware(100));
     * $route->withMiddleware(ProxyMiddleware::class);
     * $route->withMiddleware(ProxyMiddleware::class, OtherMiddleware::class);
     *
     * @param MiddlewareInterface|string ...$middleware
     * @return RouteInterface|$this
     *
     * @throws RouteException
     */
    public function withMiddleware(...$middleware): RouteInterface
    {
        $route = clone $this;
        foreach ($middleware as $item) {
            if (!is_string($item) && !$item instanceof MiddlewareInterface) {
                if (is_object($item)) {
                    $name = get_class($item);
                } else {
                    $name = gettype($item);
                }

                throw new RouteException("Invalid middleware `{$name}`.");
            }

            $route->middleware[] = $middleware;
        }

        return $route;
    }
}