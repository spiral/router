<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Router\Exception\TargetException;
use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Route;
use Spiral\Router\Target\Namespaced;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;

class NamespacedTest extends BaseTest
{
    public function testRouteException(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route(
                '/<controller>/<action>',
                new Namespaced('Spiral\Tests\Router\Fixtures')
            )
        );

        $router->handle(new ServerRequest());
    }

    public function testRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route(
                '/<controller>[/<action>[/<id>]]',
                new Namespaced('Spiral\Tests\Router\Fixtures')
            )
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello world', (string)$response->getBody());

        $response = $router->handle(new ServerRequest([], [], new Uri('/test/id/900')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('900', (string)$response->getBody());

        $response = $router->handle(new ServerRequest([], [], new Uri('/other/action')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('action!', (string)$response->getBody());
    }

    public function testBypass(): void
    {
        $this->expectException(TargetException::class);

        $n = new Namespaced('Spiral\Tests\Router\Fixtures');

        $n->getHandler($this->container, [
            'controller' => 'secret/controller',
            'action'     => null,
        ]);
    }
}
