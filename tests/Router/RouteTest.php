<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\Router\Route;
use Zend\Diactoros\ServerRequest;

class RouteTest extends TestCase
{
    public function testPrefix()
    {
        $route = new Route("/action", Call::class);
        $this->assertSame("", $route->getPrefix());
        $this->assertSame("/something", $route->withPrefix("/something")->getPrefix());
        $this->assertSame("", $route->getPrefix());
    }

    /**
     * @expectedException \Spiral\Router\Exception\RouteException
     */
    public function testContainerException()
    {
        $route = new Route("/action", Call::class);
        $route->handle(new ServerRequest());
    }
}