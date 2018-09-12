<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing\Traits;

trait DefaultsTrait
{
    /** @var array */
    protected $defaults = [];

    /**
     * Returns new route instance with forced default values.
     *
     * @param array $defaults
     * @return self
     */
    public function withDefaults(array $defaults): self
    {
        $route = clone $this;
        $route->defaults = $defaults;

        return $route;
    }

    /**
     * Get default route values.
     *
     * @return array
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }
}