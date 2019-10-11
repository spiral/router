<?php

declare(strict_types=1);

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests;

use Spiral\Router\Route;
use Spiral\Router\Target\Namespaced;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class NamespacedTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Router\Exception\UndefinedRouteException
     */
    public function testRouteException(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'group',
            new Route(
                '/<controller>/<action>',
                new Namespaced('Spiral\Router\Tests\Fixtures')
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
                new Namespaced('Spiral\Router\Tests\Fixtures')
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

    /**
     * @expectedException \Spiral\Router\Exception\TargetException
     */
    public function testBypass(): void
    {
        $n = new Namespaced('Spiral\Router\Tests\Fixtures');

        $n->getHandler($this->container, [
            'controller' => 'secret/controller',
            'action'     => null
        ]);
    }
}
