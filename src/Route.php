<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing;

use Spiral\Routing\Traits\ContainerTrait;

class Route extends AbstractRoute implements ContainerizedInterface
{
    use ContainerTrait;
}