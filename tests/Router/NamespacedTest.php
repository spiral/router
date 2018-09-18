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
use Spiral\Router\Target\Namespaced;
use Zend\Diactoros\ServerRequest;

class NamespacedTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Router\Exception\UndefinedRouteException
     */
    public function testRouteException()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'group',
            new Route(
                '/<controller>/<action>',
                new Namespaced('Spiral\Router\Tests\Fixtures')
            )
        );

        $router->handle(new ServerRequest());
    }

    public function testRoute()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'group',
            new Route(
                '/<controller>[/<action>[/<id>]]',
                new Namespaced('Spiral\Router\Tests\Fixtures')
            )
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());

        $response = $router->handle(new ServerRequest([], [], new Uri('/test/id/900')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("900", (string)$response->getBody());

        $response = $router->handle(new ServerRequest([], [], new Uri('/other/action')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("action!", (string)$response->getBody());
    }

    /**
     * @expectedException \Spiral\Router\Exception\TargetException
     */
    public function testBypass()
    {
        $n = new Namespaced('Spiral\Router\Tests\Fixtures');

        $n->getHandler($this->container, [
            'controller' => 'secret/controller',
            'action'     => null
        ]);
    }
}