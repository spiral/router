<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing\Traits;

trait NameTrait
{
    /** @var string */
    protected $name;

    /**
     * Returns new route instance with new route name.
     *
     * @param string $name
     * @return self
     */
    public function withName(string $name): self
    {
        $route = clone $this;
        $route->name = $name;

        return $route;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

}