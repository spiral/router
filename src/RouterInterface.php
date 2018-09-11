<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router;

use Psr\Http\Message\UriInterface;
use Spiral\Routing\Exceptions\RouterException;

interface RouterInterface
{
    /**
     * @param RouteInterface $route
     */
    public function addRoute(RouteInterface $route);

    /**
     * Default route is needed as fallback if no other route matched the request.
     *
     * @param RouteInterface $route
     */
    public function setDefault(RouteInterface $route);

    /**
     * Get route by it's name.
     *
     * @param string $name
     * @return RouteInterface
     *
     * @throws RouterException
     */
    public function getRoute(string $name): RouteInterface;

    /**
     * Get all registered routes.
     *
     * @return RouteInterface[]
     */
    public function getRoutes(): array;

    /**
     * Generate valid route URL using route name and set of parameters. Should support controller
     * and action name separated by ":" - in this case router should find appropriate route and
     * create url using it.
     *
     * @param string             $route      Route name.
     * @param array|\Traversable $parameters Routing parameters.
     * @return UriInterface
     *
     * @throws RouterException
     */
    public function uri(string $route, $parameters = []): UriInterface;
}