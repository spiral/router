<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing;

use Spiral\Core\ResolverInterface;
use Spiral\Router\RouteInterface;

interface CallableRouteInterface extends RouteInterface
{
    /**
     * Associated callable arguments resolver with route.
     *
     * @param ResolverInterface $resolver
     * @return RouteInterface
     */
    public function withResolver(ResolverInterface $resolver): RouteInterface;

    /**
     * Must return true if route has associated resolver.
     *
     * @return bool
     */
    public function hasResolver(): bool;
}