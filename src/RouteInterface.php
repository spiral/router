<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Routing\Exceptions\RouteException;

interface RouteInterface extends RequestHandlerInterface
{
    /**
     * Returns new route instance with new route name.
     *
     * @param string $name
     * @return RouteInterface
     */
    public function withName(string $name): RouteInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * Prefix must always include back slash at the end of prefix!
     *
     * @param string $prefix
     * @return RouteInterface
     */
    public function withPrefix(string $prefix): RouteInterface;

    /**
     * @return string
     */
    public function getPrefix(): string;

    /**
     * Returns new route instance with forced default values.
     *
     * @param array $matches
     * @return RouteInterface
     */
    public function withDefaults(array $matches): RouteInterface;

    /**
     * Get default route values.
     *
     * @return array
     */
    public function getDefaults(): array;

    /**
     * Match route against given request, must return matched route instance or return null if route does
     * not match.
     *
     * @param Request $request
     * @return self|null
     *
     * @throws RouteException
     */
    public function match(Request $request): ?RouteInterface;

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