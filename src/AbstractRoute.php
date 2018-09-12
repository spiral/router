<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing;

use Spiral\Router\RouteInterface;
use Spiral\Routing\Traits\ContainerTrait;
use Spiral\Routing\Traits\DefaultsTrait;
use Spiral\Routing\Traits\NameTrait;
use Spiral\Routing\Traits\PrefixTrait;

class AbstractRoute implements RouteInterface
{
    use NameTrait, PrefixTrait, NameTrait, DefaultsTrait, ContainerTrait;
}