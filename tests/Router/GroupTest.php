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
use Spiral\Router\Target\Group;
use Spiral\Router\Tests\Fixtures\TestController;
use Zend\Diactoros\ServerRequest;

class GroupTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Router\Exception\UndefinedRouteException
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
     * @expectedException \Spiral\Router\Exception\UndefinedRouteException
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

    /**
     * @expectedException \Spiral\Router\Exception\UriHandlerException
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
     * @expectedException \Spiral\Router\Exception\UriHandlerException
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
     * @expectedException \Spiral\Http\Exception\ClientException\NotFoundException
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