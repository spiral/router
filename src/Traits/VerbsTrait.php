<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing\Traits;

use Spiral\Router\RouteInterface;
use Spiral\Routing\Exceptions\RouteException;

trait VerbsTrait
{
    /** @var array */
    protected $verbs = RouteInterface::VERBS;

    /**
     * Attach specific list of HTTP verbs to the route.
     *
     * @param string ...$verbs
     * @return RouteInterface|$this
     *
     * @throws RouteException
     */
    public function withVerbs(string ...$verbs): RouteInterface
    {
        foreach ($verbs as &$verb) {
            $verb = strtoupper($verb);
            if (!in_array($verb, RouteInterface::VERBS)) {
                throw new RouteException("Invalid HTTP verb `{$verb}`.");
            }

            unset($verb);
        }

        $route = clone $this;
        $route->verbs = $verbs;

        return $route;
    }

    /**
     * Return list of HTTP verbs route must handle.
     *
     * @return array
     */
    public function getVerbs(): array
    {
        return $this->verbs;
    }
}