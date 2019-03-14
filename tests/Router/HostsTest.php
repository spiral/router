<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests;

use Spiral\Router\Route;
use Spiral\Router\Target\Action;
use Spiral\Router\Tests\Fixtures\TestController;
use Zend\Diactoros\ServerRequest;

class HostsTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Router\Exception\UndefinedRouteException
     */
    public function testRouteException()
    {
        $router = $this->makeRouter();
        $router->setDefault(new Route(
            '://<id>.com/',
            new Action(TestController::class, 'test')
        ));

        $match = $router->handle(new ServerRequest());
    }

    public function testRoute()
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