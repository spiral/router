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
use Spiral\Router\Tests\Diactoros\UriFactory;
use Spiral\Router\UriHandler;
use Zend\Diactoros\ServerRequest;

class RouteTest extends TestCase
{
    public function testPrefix()
    {
        $route = new Route("/action", Call::class);
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));
        $this->assertSame("", $route->getUriHandler()->getPrefix());

        $route2 = $route->withUriHandler($route->getUriHandler()->withPrefix('/something'));
        $this->assertSame("/something", $route2->getUriHandler()->getPrefix());
        $this->assertSame("", $route->getUriHandler()->getPrefix());
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
