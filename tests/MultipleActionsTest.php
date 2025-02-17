<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Route;
use Spiral\Router\Target\Action;
use Spiral\Tests\Router\Fixtures\TestController;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;

class MultipleActionsTest extends BaseTestCase
{
    public function testRouteException(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>/<id>', new Action(TestController::class, ['test', 'id'])),
        );

        $router->handle(new ServerRequest('GET', ''));
    }

    public function testRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>[/<id>]', new Action(TestController::class, ['test', 'id'])),
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('hello world', (string) $response->getBody());

        $response = $router->handle(new ServerRequest('GET', new Uri('/id/900')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('900', (string) $response->getBody());
    }

    public function testUriGeneration(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>[/<id>]', new Action(TestController::class, ['test', 'id'])),
        );

        $uri = $router->uri('action/test');
        self::assertSame('/test', $uri->getPath());

        $uri = $router->uri('action/id', ['id' => 100]);
        self::assertSame('/id/100', $uri->getPath());
    }
}
