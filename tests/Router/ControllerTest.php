<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router\Tests;

use Spiral\Router\Route;
use Spiral\Router\Target\Controller;
use Spiral\Router\Tests\Fixtures\TestController;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class ControllerTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Router\Exception\UndefinedRouteException
     */
    public function testRouteException(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>/<id>', new Controller(TestController::class))
        );

        $router->handle(new ServerRequest());
    }

    public function testRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>[/<id>]', new Controller(TestController::class))
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello world', (string)$response->getBody());

        $response = $router->handle(new ServerRequest([], [], new Uri('/echo')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('echoed', (string)$response->getBody());

        $response = $router->handle(new ServerRequest([], [], new Uri('/id/888')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('888', (string)$response->getBody());
    }

    public function testUriGeneration(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>/<id>', new Controller(TestController::class))
        );

        $uri = $router->uri('action/test');
        $this->assertSame('/test', $uri->getPath());

        $uri = $router->uri('action/id', ['id' => 100]);
        $this->assertSame('/id/100', $uri->getPath());
    }

    /**
     * @expectedException \Spiral\Http\Exception\ClientException\NotFoundException
     */
    public function testClientException(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>[/<id>]', new Controller(TestController::class))
        );

        $router->handle(new ServerRequest([], [], new Uri('/other')));
    }
}
