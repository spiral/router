<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router;

use Cocur\Slugify\Slugify;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Http\CallableHandler;
use Spiral\Router\Exception\RouteException;
use Spiral\Router\Traits\PipelineTrait;

/**
 * Default route provides ability to route request to a given callable handler.
 *
 * Examples:
 *
 * new Route("/login", function(){
 *   return "hello";
 * });
 * new Route("/login", new Handler());
 * new Route("/login", \App\Handlers\Handler::class);
 *
 * new Route("/login", new Action(\App\Controllers|HomeController::class, "login");
 * new Route("/<controller>/<action>/<id>", new Namespaced("\App\Controllers");
 * new Route("/signup/<action>", new Controller(\App\Controllers\SignupController::class);
 * new Route("://<domain>/info", new Action(\App\Controllers|ProfileController::class, "info");
 * new Route("/<controller>/<action>/<id>", new Group(["profile" =>
 * \App\Controllers|ProfileController::class]);
 */
class Route extends AbstractRoute implements ContainerizedInterface
{
    use PipelineTrait;

    const ROUTE_ATTRIBUTE = 'route';

    /** @var string|callable|RequestHandlerInterface|TargetInterface */
    private $target;

    /** @var RequestHandlerInterface */
    private $requestHandler;

    /**
     * @param string                                  $pattern  Uri pattern.
     * @param string|callable|RequestHandlerInterface $target   Callable route target.
     * @param array                                   $defaults Default value set.
     */
    public function __construct(string $pattern, $target, array $defaults = [])
    {
        $this->target = $target;
        if ($target instanceof TargetInterface) {
            $this->defaults = array_merge($defaults, $target->getDefaults());
            $this->uriHandler = new UriHandler($pattern, new Slugify(), $target->getConstrains());
        } else {
            parent::__construct($pattern, $defaults);
        }
    }

    /**
     * Associated route with given container.
     *
     * @param ContainerInterface $container
     *
     * @return ContainerizedInterface|$this
     */
    public function withContainer(ContainerInterface $container): ContainerizedInterface
    {
        $route = clone $this;
        $route->container = $container;
        if ($route->target instanceof TargetInterface) {
            $route->target = clone $route->target;
        }

        $route->pipeline = $route->makePipeline();

        return $route;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws RouteException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (empty($this->requestHandler)) {
            $this->requestHandler = $this->requestHandler();
        }

        return $this->pipeline->process(
            $request->withAttribute(self::ROUTE_ATTRIBUTE, $this),
            $this->requestHandler
        );
    }

    /**
     * @return RequestHandlerInterface
     *
     * @throws RouteException
     */
    protected function requestHandler(): RequestHandlerInterface
    {
        if (!$this->hasContainer()) {
            throw new RouteException("Unable to configure route pipeline without associated container.");
        }

        if ($this->target instanceof TargetInterface) {
            return $this->target->getHandler($this->container, $this->matches);
        }

        if ($this->target instanceof RequestHandlerInterface) {
            return $this->target;
        }

        try {
            if (is_object($this->target) || is_array($this->target)) {
                $target = $this->target;
            } else {
                $target = $this->container->get($this->target);
            }

            if ($target instanceof RequestHandlerInterface) {
                return $target;
            }

            return new CallableHandler(
                $target,
                $this->container->get(ResponseFactoryInterface::class)
            );
        } catch (ContainerExceptionInterface $e) {
            throw new RouteException($e->getMessage(), $e->getCode(), $e);
        }
    }
}