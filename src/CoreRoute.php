<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing;

use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\CoreInterface;
use Spiral\Routing\Traits\PipelineTrait;

/**
 * CoreRoute provides ability to invoke controller actions or group of controllers.
 */
abstract class CoreRoute extends AbstractRoute
{
    use PipelineTrait;

    /** @var string */
    private $target;

    /** @var CoreInterface */
    private $core;

    /**
     * @param string                                  $pattern  Uri pattern.
     * @param string|callable|RequestHandlerInterface $target   Target controller action pair, or controller group.
     * @param array                                   $defaults Default value set.
     */
    public function __construct(string $pattern, string $target, array $defaults = [])
    {
        parent::__construct($pattern, $defaults);
        $this->target = $target;
    }
}