<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing;

use Spiral\Core\CoreInterface;
use Spiral\Routing\Traits\PipelineTrait;

/**
 * CoreRoute provides ability to invoke controller actions or group of controllers.
 *
 * @todo: add examples
 */
abstract class CoreRoute extends AbstractRoute
{
    use PipelineTrait;

    /** @var DDD */
    private $target;

    /** @var CoreInterface */
    private $core;

    /**
     * @param string $pattern  Uri pattern.
     * @param DDD    $target   Defines set of controllers (statically or dynamically) route can invoke.
     * @param array  $defaults Default value set.
     */
    public function __construct(string $pattern, DDD $target, array $defaults = [])
    {
        parent::__construct($pattern, array_merge($defaults, $target->getDefaults()));
        $this->target = $target;
    }
}