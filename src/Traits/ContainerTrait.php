<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing\Traits;

use Psr\Container\ContainerInterface;

trait ContainerTrait
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * Associated route with given container.
     *
     * @param ContainerInterface $container
     * @return self
     */
    public function withContainer(ContainerInterface $container): self
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