<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests;

use Spiral\Router\Route;

class RouterTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Router\Exceptions\RouterException
     */
    public function testDuplicate()
    {
        $router = $this->makeRouter();

        $router->addRoute('name', new Route('/', Call::class));
        $router->addRoute('name', new Route('/', Call::class));
    }

    public function testGetRoutes()
    {
        $router = $this->makeRouter();

        $router->addRoute('name', new Route('/', Call::class));
        $this->assertCount(1, $router->getRoutes());
    }

    public function testDefault()
    {
        $router = $this->makeRouter();

        $router->addRoute('name', new Route('/', Call::class));
        $router->setDefault(new Route('/', Call::class));

        $this->assertCount(2, $router->getRoutes());
    }

    /**
     * @expectedException \Spiral\Router\Exceptions\RouteNotFoundException
     */
    public function testCastError()
    {
        $router = $this->makeRouter();
        $router->uri('name/?broken');
    }
}