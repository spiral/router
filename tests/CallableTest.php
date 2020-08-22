<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Router\Exception\RouteException;
use Spiral\Router\Route;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;

class CallableTest extends BaseTest
{
    public function testFunctionRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/something', function () {
                return 'hello world';
            })
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/something')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello world', (string)$response->getBody());
    }

    public function testObjectRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/something', new Call())
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/something')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('invoked', (string)$response->getBody());
    }

    public function testObjectViaContainerRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/something', Call::class)
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/something')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('invoked', (string)$response->getBody());
    }

    public function testHandlerRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/something', new Handler())
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/something')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('handler', (string)$response->getBody());
    }

    public function testHandlerViaContainerRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/something', Handler::class)
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/something')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('handler', (string)$response->getBody());
    }

    public function testInvalidTarget(): void
    {
        $this->expectException(RouteException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/something', 'something')
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/something')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('handler', (string)$response->getBody());
    }
}
