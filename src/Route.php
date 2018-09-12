<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\Exceptions\Container\ContainerException;
use Spiral\Http\CallableHandler;
use Spiral\Routing\Exceptions\RouteException;
use Spiral\Routing\Traits\PipelineTrait;

/**
 * Default route provides ability to route request to a given callable handler.
 *
 * @todo: add examples
 */
class Route extends AbstractRoute implements ContainerizedInterface
{
    use PipelineTrait;

    /** @var string|callable|RequestHandlerInterface */
    private $target;

    /** @var RequestHandlerInterface|null */
    private $handler;

    /**
     * @param string                                  $pattern  Uri pattern.
     * @param string|callable|RequestHandlerInterface $target   Callable route target.
     * @param array                                   $defaults Default value set.
     */
    public function __construct(string $pattern, $target, array $defaults = [])
    {
        if ($target instanceof TargetInterface) {
            $defaults = array_merge($defaults, $target);
        }

        parent::__construct($pattern, $defaults);
        $this->target = $target;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     *
     * @throws RouteException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (empty($this->handler)) {
            $this->handler = $this->makeHandler();
        }

        return $this->makePipeline()->process($request, $this->handler);
    }

    /**
     * @return RequestHandlerInterface
     *
     * @throws RouteException
     */
    protected function makeHandler(): RequestHandlerInterface
    {
        if ($this->target instanceof RequestHandlerInterface) {
            return $this->target;
        }

        if (!$this->hasContainer()) {
            throw new RouteException("Unable to configure route pipeline without associated container.");
        }

        if ($this->target instanceof TargetInterface) {
            // todo: handle TargetInterface
        }

        try {
            if (is_object($this->target) || is_array($this->target)) {
                $target = $this->target;
            } else {
                $target = $this->container->get($this->target);
            }

            return new CallableHandler($target, $this->container->get(RequestHandlerInterface::class));
        } catch (ContainerException $e) {
            throw new RouteException($e->getMessage(), $e->getCode(), $e);
        }
    }
}