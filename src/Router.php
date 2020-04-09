<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Router\Exception\RouteException;
use Spiral\Router\Exception\RouteNotFoundException;
use Spiral\Router\Exception\RouterException;
use Spiral\Router\Exception\UndefinedRouteException;

/**
 * Manages set of routes.
 */
final class Router implements RouterInterface
{
    // attribute to store active route in request
    public const ROUTE_ATTRIBUTE = 'route';

    // attribute to store active route in request
    public const ROUTE_NAME = 'routeName';

    // attribute to store active route in request
    public const ROUTE_MATCHES = 'matches';

    /** @var string */
    private $basePath = '/';

    /** @var RouteInterface[] */
    private $routes = [];

    /** @var RouteInterface */
    private $default = null;

    /** @var UriHandler */
    private $uriHandler;

    /** @var ContainerInterface */
    private $container;

    /**
     * @param string             $basePath
     * @param UriHandler         $uriHandler
     * @param ContainerInterface $container
     */
    public function __construct(string $basePath, UriHandler $uriHandler, ContainerInterface $container)
    {
        $this->basePath = '/' . ltrim($basePath, '/');
        $this->uriHandler = $uriHandler;
        $this->container = $container;
    }

    /**
     * @inheritdoc
     *
     * @throws RouteNotFoundException
     * @throws RouterException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $route = $this->matchRoute($request, $routeName);
        } catch (RouteException $e) {
            throw new RouterException('Invalid route definition', $e->getCode(), $e);
        }

        if ($route === null) {
            throw new RouteNotFoundException($request->getUri());
        }

        return $route->handle(
            $request
                ->withAttribute(self::ROUTE_ATTRIBUTE, $route)
                ->withAttribute(self::ROUTE_NAME, $routeName)
                ->withAttribute(self::ROUTE_MATCHES, $route->getMatches() ?? [])
        );
    }

    /**
     * @inheritdoc
     *
     * @deprecated see setRoute()
     */
    public function addRoute(string $name, RouteInterface $route): void
    {
        //Each added route must inherit basePath prefix
        $this->setRoute($name, $route);
    }

    /**
     * @inheritdoc
     */
    public function setRoute(string $name, RouteInterface $route): void
    {
        // each route must inherit basePath prefix
        $this->routes[$name] = $this->configure($route);
    }

    /**
     * @inheritdoc
     */
    public function setDefault(RouteInterface $route): void
    {
        $this->default = $this->configure($route);
    }

    /**
     * @inheritdoc
     */
    public function getRoute(string $name): RouteInterface
    {
        if (isset($this->routes[$name])) {
            return $this->routes[$name];
        }

        throw new UndefinedRouteException("Undefined route `{$name}`");
    }

    /**
     * @inheritdoc
     */
    public function getRoutes(): array
    {
        if (!empty($this->default)) {
            return $this->routes + [null => $this->default];
        }

        return $this->routes;
    }

    /**
     * @inheritdoc
     */
    public function uri(string $route, $parameters = []): UriInterface
    {
        try {
            return $this->getRoute($route)->uri($parameters);
        } catch (UndefinedRouteException $e) {
            //In some cases route name can be provided as controller:action pair, we can try to
            //generate such route automatically based on our default/fallback route
            return $this->castRoute($route)->uri($parameters);
        }
    }

    /**
     * Find route matched for given request.
     *
     * @param ServerRequestInterface $request
     * @return null|RouteInterface
     */
    protected function matchRoute(ServerRequestInterface $request, string &$routeName = null): ?RouteInterface
    {
        foreach ($this->routes as $name => $route) {
            // Matched route will return new route instance with matched parameters
            $matched = $route->match($request);

            if ($matched !== null) {
                $routeName = $name;
                return $matched;
            }
        }

        if ($this->default !== null) {
            return $this->default->match($request);
        }

        // unable to match any route
        return null;
    }

    /**
     * Configure route with needed dependencies.
     *
     * @param RouteInterface $route
     * @return RouteInterface
     */
    protected function configure(RouteInterface $route): RouteInterface
    {
        if ($route instanceof ContainerizedInterface && !$route->hasContainer()) {
            // isolating route in a given container
            $route = $route->withContainer($this->container);
        }

        return $route->withUriHandler($this->uriHandler->withPrefix($this->basePath));
    }

    /**
     * Locates appropriate route by name. Support dynamic route allocation using following pattern:
     * Named route:   `name/controller:action`
     * Default route: `controller:action`
     * Only action:   `name/action`
     *
     * @param string $route
     * @return RouteInterface
     *
     * @throws UndefinedRouteException
     */
    protected function castRoute(string $route): RouteInterface
    {
        if (
            !preg_match(
                '/^(?:(?P<name>[^\/]+)\/)?(?:(?P<controller>[^:]+):+)?(?P<action>[a-z_\-]+)$/i',
                $route,
                $matches
            )
        ) {
            throw new UndefinedRouteException(
                "Unable to locate route or use default route with 'name/controller:action' pattern"
            );
        }

        if (!empty($matches['name'])) {
            $routeObject = $this->getRoute($matches['name']);
        } elseif ($this->default !== null) {
            $routeObject = $this->default;
        } else {
            throw new UndefinedRouteException("Unable to locate route candidate for `{$route}`");
        }

        return $routeObject->withDefaults(
            [
                'controller' => $matches['controller'],
                'action'     => $matches['action']
            ]
        );
    }
}
