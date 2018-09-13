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
use Spiral\Router\Targets\Group;
use Spiral\Router\Tests\Fixtures\TestController;
use Zend\Diactoros\ServerRequest;

class GroupTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Router\Exceptions\RouteNotFoundException
     */
    public function testRouteException()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'group',
            new Route('/<controller>/<action>', new Group([
                'test' => TestController::class
            ]))
        );

        $router->handle(new ServerRequest());
    }

    public function testRoute()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class
            ]))
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());

        $response = $router->handle(new ServerRequest([], [], new Uri('/test/id/900')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("900", (string)$response->getBody());
    }

    /**
     * @expectedException \Spiral\Router\Exceptions\RouteNotFoundException
     */
    public function testRouteOther()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class
            ]))
        );

        $router->handle(new ServerRequest([], [], new Uri('/other')));
    }

    public function testUriGeneration()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class
            ]))
        );

        $uri = $router->uri('group/test:test');
        $this->assertSame('/test/test', $uri->getPath());

        $uri = $router->uri('group/test:id', ['id' => 100]);
        $this->assertSame('/test/id/100', $uri->getPath());

        $uri = $router->getRoute('group')->uri(['test', 'id', 100]);
        $this->assertSame('/test/id/100', $uri->getPath());
    }

    /**
     * @expectedException \Spiral\Router\Exceptions\UriHandlerException
     */
    public function testUriInvalid()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class
            ]))
        );

        $uri = $router->uri('group/test');
    }

    /**
     * @expectedException \Spiral\Router\Exceptions\UriHandlerException
     */
    public function testUriInvalidNoAction()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class
            ]))
        );

        $uri = $router->getRoute('group')->uri(['controller' => 'test']);
    }

    /**
     * @expectedException \Spiral\Http\Exceptions\ClientExceptions\NotFoundException
     */
    public function testClientException()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class
            ]))
        );

        $router->handle(new ServerRequest([], [], new Uri('/test/other')));
    }
}