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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Http\CallableHandler;
use Spiral\Router\Exceptions\RouteException;
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
 * new Route("/<controller>/<action>/<id>", new Group("\App\Controllers");
 * new Route("/signup/<action>", new Controller(\App\Controllers\SignUpController::class);
 * new Route("://<domain>/info", new Action(\App\Controllers|ProfileController::class, "info");
 */
class Route extends AbstractRoute implements ContainerizedInterface
{
    use PipelineTrait;

    const ROUTE_ATTRIBUTE = 'route';

    /** @var string|callable|RequestHandlerInterface|TargetInterface */
    private $target;

    /**
     * @param string                                  $pattern  Uri pattern.
     * @param string|callable|RequestHandlerInterface $target   Callable route target.
     * @param array                                   $defaults Default value set.
     */
    public function __construct(string $pattern, $target, array $defaults = [])
    {
        if ($target instanceof TargetInterface) {
            $this->defaults = array_merge($defaults, $target->getDefaults());
            $this->handler = new UriHandler($pattern, new Slugify(), $target->getConstrains());
        } else {
            parent::__construct($pattern, $defaults);
        }
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
        if (empty($this->handler)) {
            $this->handler = $this->makeHandler();
        }

        return $this->makePipeline()->process(
            $request->withAttribute(self::ROUTE_ATTRIBUTE, $this),
            $this->handler
        );
    }

    /**
     * @return RequestHandlerInterface
     *
     * @throws RouteException
     */
    protected function makeHandler(): RequestHandlerInterface
    {
        if (!$this->hasContainer()) {
            throw new RouteException("Unable to configure route pipeline without associated container.");
        }

        if ($this->target instanceof TargetInterface) {
            return $this->target->makeHandler($this->container, $this->matches);
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

            return new CallableHandler(
                $target,
                $this->container->get(RequestHandlerInterface::class)
            );
        } catch (ContainerExceptionInterface $e) {
            throw new RouteException($e->getMessage(), $e->getCode(), $e);
        }
    }
}