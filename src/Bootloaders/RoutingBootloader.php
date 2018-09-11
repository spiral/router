<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing\Bootloaders;

use Psr\Container\ContainerInterface;
use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;

class RoutingBootloader extends Bootloader
{
    const SINGLETONS = [
        RouterInterface::class => [self::class, 'makeRouter']
    ];

    /**
     * @param HttpConfig         $config
     * @param ContainerInterface $container
     * @return RouterInterface
     */
    protected function makeRouter(HttpConfig $config, ContainerInterface $container): RouterInterface
    {
        return new Router($config->basePath(), $container);
    }
}