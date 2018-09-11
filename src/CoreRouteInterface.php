<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing;

use Spiral\Core\CoreInterface;
use Spiral\Router\RouteInterface;

interface CoreRouteInterface extends RouteInterface
{
    /**
     * Associate HMVC core with the route.
     *
     * @param CoreInterface $core
     * @return RouteInterface
     */
    public function withCore(CoreInterface $core): RouteInterface;

    /**
     * Must return true if route has associated core.
     *
     * @return bool
     */
    public function hasCore(): bool;
}