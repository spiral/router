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
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exceptions\Container\NotFoundException;
use Spiral\Core\ResolverInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Routing\CallableRouteInterface;
use Spiral\Routing\Exceptions\RouteNotFoundException;
use Spiral\Routing\HMVCRouteInterface;

/**
 * Manages set of routes. Container must include bindings to:
 * - ResolverInterface for callable routes.
 * - CoreInterface     for HMVC routes
 */
class Router implements RouterInterface, RequestHandlerInterface
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
    public function addRoute(RouteInterface $route)
    {
        //Each added route must inherit basePath prefix
        $this->routes[] = $this->configure($route);
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
        if (!empty($this->default) && $this->default->getName() == $name) {
            return $this->default;
        }

        foreach ($this->routes as $route) {
            if ($route->getName() == $name) {
                return $route;
            }
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
     * @return null|RouteInterface
     */
    protected function matchRoute(ServerRequestInterface $request): ?RouteInterface
    {
        foreach ($this->routes as $route) {
            //Route might return altered version on matching (route with populated parameters)
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
     * @return RouteInterface
     */
    protected function configure(RouteInterface $route): RouteInterface
    {
        $route = $route->withPrefix($this->basePath);

        if ($route instanceof HMVCRouteInterface && !$route->hasCore()) {
            $route = $route->withCore($this->container->get(CoreInterface::class));
        }

        if ($route instanceof CallableRouteInterface && !$route->hasResolver()) {
            $route = $route->withResolver($this->container->get(ResolverInterface::class));
        }

        return $route;
    }

    /**
     * Helper function used to reconfigure default route (usually controller route) with set of
     * parameters related to selected controller and action.
     *
     * @param string $route
     *
     * @return RouteInterface
     * @throws RouteNotFoundException
     */
    protected function castRoute(string $route): RouteInterface
    {
        //Will be handled via default route where route name is specified as controller::action
        if (strpos($route, ':') === false) {
            throw new RouteNotFoundException(
                "Unable to locate route or use default route with 'controller:action' pattern"
            );
        }

        if (empty($this->default)) {
            throw new RouteNotFoundException("Default route is missing");
        }

        //We can fetch controller and action names from url
        list($controller, $action) = explode(':', str_replace(['/', '::'], ':', $route));

        //Let's create new route for a controller and action
        $route = $this->default->withName($route)->withDefaults([
            'controller' => $controller,
            'action'     => $action
        ]);

        //For future requests
        $this->addRoute($route);

        return $route;
    }
}