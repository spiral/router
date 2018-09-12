<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing;

use Psr\Container\ContainerInterface;
use Spiral\Router\RouteInterface;

interface ContainerizedInterface extends RouteInterface
{
    /**
     * Associated route with given container.
     *
     * @param ContainerInterface $container
     * @return ContainerizedInterface|$this
     */
    public function withContainer(ContainerInterface $container): ContainerizedInterface;

    /**
     * Indicates that route has associated container.
     *
     * @return bool
     */
    public function hasContainer(): bool;
}