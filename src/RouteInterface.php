<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Routing\Exceptions\RouteException;

/**
 * Route provides ability to handle incoming request based on defined pattern. Each route must be isolated using
 * given container.
 */
interface RouteInterface extends RequestHandlerInterface
{
    /**
     * List of possible verbs for the route.
     */
    const VERBS = ['GET', 'POST', 'PUT', 'PATCH', 'OPTIONS', 'DELETE'];

    /**
     * Return list of HTTP verbs route must handle.
     *
     * @return array
     */
    public function getVerbs(): array;

    /**
     * Returns new route instance with new route name.
     *
     * @param string $name
     * @return self
     */
    public function withName(string $name): self;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * Prefix must always include back slash at the end of prefix!
     *
     * @param string $prefix
     * @return self
     */
    public function withPrefix(string $prefix): self;

    /**
     * @return string
     */
    public function getPrefix(): string;

    /**
     * Returns new route instance with forced default values.
     *
     * @param array $defaults
     * @return self
     */
    public function withDefaults(array $defaults): self;

    /**
     * Get default route values.
     *
     * @return array
     */
    public function getDefaults(): array;

    /**
     * Associated route with given container.
     *
     * @param ContainerInterface $container
     * @return self
     */
    public function withContainer(ContainerInterface $container): self;

    /**
     * Indicates that route has associated container.
     *
     * @return bool
     */
    public function hasContainer(): bool;

    /**
     * Match route against given request, must return matched route instance or return null if route does
     * not match.
     *
     * @param Request $request
     * @return self|null
     *
     * @throws RouteException
     */
    public function match(Request $request): ?self;

    /**
     * Generate valid route URL using set of routing parameters.
     *
     * @param array|\Traversable $parameters
     * @return UriInterface
     *
     * @throws RouteException
     */
    public function uri($parameters = []): UriInterface;
}