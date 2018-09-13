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

class MultipleActionsTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Router\Exceptions\RouteNotFoundException
     */
    public function testRouteException()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/<action>/<id>', new Action(TestController::class, ['test', 'id']))
        );

        $router->handle(new ServerRequest());
    }

    public function testSingleActionRoute()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/<action>[/<id>]', new Action(TestController::class, ['test', 'id']))
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());

        $response = $router->handle(new ServerRequest([], [], new Uri('/id/900')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("900", (string)$response->getBody());
    }

    public function testUriGeneration()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/<action>[/<id>]', new Action(TestController::class, ['test', 'id']))
        );

        $uri = $router->uri('action/test');
        $this->assertSame('/test', $uri->getPath());

        $uri = $router->uri('action/id', ['id' => 100]);
        $this->assertSame('/id/100', $uri->getPath());
    }
}