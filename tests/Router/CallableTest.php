<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Router\Route;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class CallableTest extends BaseTest
{
    public function testFunctionRoute()
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/something', function () {
                return "hello world";
            })
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/something')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());
    }

    public function testObjectRoute()
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/something', new Call())
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/something')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("invoked", (string)$response->getBody());
    }

    public function testObjectViaContainerRoute()
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/something', Call::class)
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/something')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("invoked", (string)$response->getBody());
    }

    public function testHandlerRoute()
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/something', new Handler())
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/something')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("handler", (string)$response->getBody());
    }

    public function testHandlerViaContainerRoute()
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/something', Handler::class)
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/something')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("handler", (string)$response->getBody());
    }

    /**
     * @expectedException \Spiral\Router\Exception\RouteException
     */
    public function testInvalidTarget()
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/something', "something")
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/something')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("handler", (string)$response->getBody());
    }
}
