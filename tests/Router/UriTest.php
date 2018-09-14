<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests;

use Spiral\Router\Route;
use Spiral\Router\Targets\Group;
use Spiral\Router\Tests\Fixtures\TestController;

class UriTest extends BaseTest
{
    public function testCastRoute()
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
    }

    public function testQuery()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class
            ]))
        );

        $uri = $router->uri('group/test:id', ['id' => 100, 'data' => 'hello']);
        $this->assertSame('/test/id/100', $uri->getPath());
        $this->assertSame('data=hello', $uri->getQuery());
    }

    public function testDirect()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class
            ]))
        );

        $uri = $router->getRoute('group')->uri(['test', 'id', 100]);
        $this->assertSame('/test/id/100', $uri->getPath());
    }

    public function testSlug()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'group',
            new Route('/<controller>[/<action>[/<id>[-<title>]]]', new Group([
                'test' => TestController::class
            ]))
        );

        $uri = $router->getRoute('group')->uri(['test', 'id', 100, 'Hello World']);
        $this->assertSame('/test/id/100-hello-world', $uri->getPath());
    }

    public function testSlugDefault()
    {
        $router = $this->makeRouter();
        $router->setDefault(
            new Route('/<controller>[/<action>[/<id>[-<title>]]]', new Group([
                'test' => TestController::class
            ]))
        );

        $uri = $router->uri('test:id', ['id' => 100, 'title' => 'Hello World']);
        $this->assertSame('/test/id/100-hello-world', $uri->getPath());
    }

    /**
     * @expectedException \Spiral\Router\Exceptions\RouteNotFoundException
     */
    public function testSlugNoDefault()
    {
        $router = $this->makeRouter();

        $uri = $router->uri('test:id', ['id' => 100, 'title' => 'Hello World']);
        $this->assertSame('/test/id/100-hello-world', $uri->getPath());
    }
}