<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Core\Exceptions\Container\NotFoundException;
use Spiral\Core\ScopeInterface;
use Spiral\Router\Exceptions\RouteNotFoundException;
use Spiral\Router\Exceptions\RouterException;

/**
 * Manages set of routes.
 */
class Router implements RouterInterface
{
    /** @var string */
    private $basePath = '/';

    /** @var RouteInterface[] */
    private $routes = [];

    /** @var RouteInterface */
    private $default = null;

    /** @var ContainerInterface */
    private $container;

    /**
     * @param string             $basePath
     * @param ContainerInterface $container
     */
    public function __construct(string $basePath, ContainerInterface $container)
    {
        $this->basePath = $basePath;
        $this->container = $container;
    }

    /**
     * @inheritdoc
     *
     * @throws NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route = $this->matchRoute($request);

        if (empty($route)) {
            throw new NotFoundException();
        }

        return $this->container->get(ScopeInterface::class)->runScope(
            [RouteInterface::class => $this],
            function () use ($route, $request) {
                return $route->handle($request);
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function addRoute(string $name, RouteInterface $route)
    {
        if (isset($this->routes[$name])) {
            throw new RouterException("Duplicate route `{$name}`.");
        }

        //Each added route must inherit basePath prefix
        $this->routes[$name] = $this->configure($route);
    }

    /**
     * @inheritdoc
     */
    public function setDefault(RouteInterface $route)
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

        throw new RouteNotFoundException("Undefined route `{$name}`");
    }

    /**
     * @inheritdoc
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @inheritdoc
     */
    public function uri(string $route, $parameters = []): UriInterface
    {
        try {
            return $this->getRoute($route)->uri($parameters);
        } catch (RouteNotFoundException $e) {
            //In some cases route name can be provided as controller:action pair, we can try to
            //generate such route automatically based on our default/fallback route
            return $this->castRoute($route)->uri($parameters);
        }
    }

    /**
     * Find route matched for given request.
     *
     * @param ServerRequestInterface $request
     *
     * @return null|RouteInterface
     */
    protected function matchRoute(ServerRequestInterface $request): ?RouteInterface
    {
        foreach ($this->routes as $route) {
            // Matched route will return new route instance with matched parameters
            $matched = $route->match($request);

            if (!empty($matched)) {
                return $matched;
            }
        }

        if (!empty($this->default)) {
            return $this->default->match($request);
        }

        // unable to match any route
        return null;
    }

    /**
     * Configure route with needed dependencies.
     *
     * @param RouteInterface $route
     *
     * @return RouteInterface
     */
    protected function configure(RouteInterface $route): RouteInterface
    {
        if ($route instanceof ContainerizedInterface && !$route->hasContainer()) {
            // isolating route in a given container
            $route = $route->withContainer($this->container);
        }

        return $route->withPrefix($this->basePath);
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
     * @throws RouteNotFoundException
     */
    protected function castRoute(string $route): RouteInterface
    {
        if (!preg_match(
            '/^(?:(?P<name>[^\/]+)\/)?(?:(?P<controller>[^:]+):+)?(?P<action>[a-z_\-]+)$/i',
            $route,
            $matches
        )) {
            throw new RouteNotFoundException(
                "Unable to locate route or use default route with 'name/controller:action' pattern."
            );
        }

        if (!empty($matches['name'])) {
            $route = $this->getRoute($matches['name']);
        } elseif (!empty($this->default)) {
            $route = $this->default;
        } else {
            throw new RouteNotFoundException("Unable to locate route candidate for `{$route}`.");
        }

        return $route->withDefaults(['controller' => $matches['controller'], 'action' => $matches['action']]);
    }
}