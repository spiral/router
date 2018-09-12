<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing\Traits;

use Psr\Http\Server\MiddlewareInterface;
use Spiral\Core\Exceptions\Container\ContainerException;
use Spiral\Http\Pipeline;
use Spiral\Router\RouteInterface;
use Spiral\Routing\Exceptions\RouteException;

trait PipelineTrait
{
    use ContainerTrait;

    /** @var Pipeline */
    protected $pipeline;

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
        $route->pipeline = null;
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

    /**
     * Get associated route pipeline.
     *
     * @return Pipeline
     * @throws RouteException
     */
    protected function makePipeline(): Pipeline
    {
        if (!empty($this->pipeline)) {
            return $this->pipeline;
        }

        if (!$this->hasContainer()) {
            throw new RouteException("Unable to configure route pipeline without associated container.");
        }

        try {
            $this->pipeline = $this->container->get(Pipeline::class);

            foreach ($this->middleware as $middleware) {
                if ($middleware instanceof MiddlewareInterface) {
                    $this->pipeline->pushMiddleware($middleware);
                } else {
                    // dynamically resolved
                    $this->pipeline->pushMiddleware($this->container->get($middleware));
                }
            }
        } catch (ContainerException $e) {
            throw new RouteException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->pipeline;
    }
}