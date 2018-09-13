<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests;

use Spiral\Http\Uri;
use Spiral\Router\Route;
use Spiral\Router\Targets\Action;
use Spiral\Router\Tests\Fixtures\TestController;
use Zend\Diactoros\ServerRequest;

class SingleActionTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Router\Exceptions\RouteNotFoundException
     */
    public function testRouteException()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/test', new Action(TestController::class, 'test'))
        );

        $router->handle(new ServerRequest());
    }

    public function testSingleActionRoute()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/test', new Action(TestController::class, 'test'))
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());
    }

    public function testParametrizedActionRoute()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/test/<id:\d+>', new Action(TestController::class, 'id'))
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test/100')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("100", (string)$response->getBody());
    }

    /**
     * @expectedException \Spiral\Router\Exceptions\RouteNotFoundException
     */
    public function testParametrizedActionRouteNotFound()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/test/<id:\d+>', new Action(TestController::class, 'id'))
        );

        $router->handle(new ServerRequest([], [], new Uri('/test/abc')));
    }

    public function testUriGeneration()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/test/<id>', new Action(TestController::class, 'id'))
        );

        $uri = $router->uri('action');
        $this->assertSame('/test', $uri->getPath());

        $uri = $router->uri('action', ['id' => 100]);
        $this->assertSame('/test/100', $uri->getPath());
    }

    /**
     * @expectedException \Spiral\Router\Exceptions\RouteNotFoundException
     */
    public function testWrongActionRoute()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/test', new Action(TestController::class, 'test'))
        );

        $router->handle(new ServerRequest([], [], new Uri('/other')));
    }
}