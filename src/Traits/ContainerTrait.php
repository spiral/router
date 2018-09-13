<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Traits;

use Psr\Container\ContainerInterface;

trait ContainerTrait
{
    /** @var ContainerInterface */
    protected $container;

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