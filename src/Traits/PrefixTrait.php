<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing\Traits;

trait PrefixTrait
{
    /** @var string */
    protected $prefix;

    /**
     * Prefix must always include back slash at the end of prefix!
     *
     * @param string $prefix
     * @return self
     */
    public function withPrefix(string $prefix): self
    {
        $route = clone $this;
        $route->prefix = $prefix;

        return $route;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}