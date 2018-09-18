<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router;

use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Router\Exception\RouteException;
use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Exception\RouterException;

interface RouterInterface extends RequestHandlerInterface
{
    /**
     * @param string         $name
     * @param RouteInterface $route
     *
     * @throws RouterException
     */
    public function addRoute(string $name, RouteInterface $route);

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
     *
     * @return RouteInterface
     *
     * @throws UndefinedRouteException
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
     *
     * @return UriInterface
     *
     * @throws RouteException
     * @throws UndefinedRouteException
     */
    public function uri(string $route, $parameters = []): UriInterface;
}