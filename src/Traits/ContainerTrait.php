<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Traits;

use Psr\Container\ContainerInterface;
use Spiral\Router\ContainerizedInterface;

trait ContainerTrait
{
    /** @var ContainerInterface */
    protected $container;

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

        return $route;
    }

    /**
     * Indicates that route has associated container.
     *
     * @return bool
     */
    public function hasContainer(): bool
    {
        return !empty($this->container);
    }
}