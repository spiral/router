<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Route;
use Spiral\Router\Target\Action;
use Spiral\Tests\Router\Fixtures\TestController;
use Laminas\Diactoros\ServerRequest;

class HostsTest extends BaseTest
{
    public function testRouteException(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->setDefault(new Route(
            '://<id>.com/',
            new Action(TestController::class, 'test')
        ));

        $match = $router->handle(new ServerRequest());
    }

    public function testRoute(): void
    {
        $router = $this->makeRouter();
        $router->setDefault(new Route(
            '//<id>.com/',
            new Action(TestController::class, 'test')
        ));

        $this->assertNotNull(
            $r = $router->handle(new ServerRequest([], [], 'http://domain.com/'))
        );

        $this->assertSame(200, $r->getStatusCode());
        $this->assertSame('hello world', (string)$r->getBody());

        $this->assertNotNull(
            $r = $router->handle(new ServerRequest([], [], 'https://domain.com/'))
        );

        $this->assertSame(200, $r->getStatusCode());
        $this->assertSame('hello world', (string)$r->getBody());
    }
}
