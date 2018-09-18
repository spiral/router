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
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Http\Uri;
use Spiral\Router\Route;
use Spiral\Router\Target\Group;
use Spiral\Router\Tests\Fixtures\TestController;
use Zend\Diactoros\ServerRequest;

class MiddlewareTest extends BaseTest
{
    public function testRoute()
    {
        $router = $this->makeRouter();

        $router->addRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class
            ])))->withMiddleware(HeaderMiddleware::class)
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());
        $this->assertSame("Value*", $response->getHeaderLine('Header'));

        $r = $router->getRoute('group')->withMiddleware(HeaderMiddleware::class);

        $r = $r->match(new ServerRequest([], [], new Uri('/test')));
        $response = $r->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());
        $this->assertSame("Value*,Value*", $response->getHeaderLine('Header'));
    }

    public function testRouteRuntime()
    {
        $router = $this->makeRouter();

        $router->addRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class
            ])))->withMiddleware(new HeaderMiddleware())
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());
        $this->assertSame("Value*", $response->getHeaderLine('Header'));
    }

    public function testRouteArray()
    {
        $router = $this->makeRouter();

        $router->addRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class
            ])))->withMiddleware([new HeaderMiddleware(), HeaderMiddleware::class])
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());
        $this->assertSame("Value*,Value*", $response->getHeaderLine('Header'));

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());
        $this->assertSame("Value*,Value*", $response->getHeaderLine('Header'));
    }

    /**
     * @expectedException \Spiral\Router\Exception\RouteException
     */
    public function testInvalid()
    {
        $router = $this->makeRouter();

        $router->addRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class
            ])))->withMiddleware($this)
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());
        $this->assertSame("Value*,Value*", $response->getHeaderLine('Header'));
    }

    /**
     * @expectedException \Spiral\Router\Exception\RouteException
     */
    public function testInvalid2()
    {
        $router = $this->makeRouter();

        $router->addRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class
            ])))->withMiddleware([[]])
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());
        $this->assertSame("Value*,Value*", $response->getHeaderLine('Header'));
    }

    /**
     * @expectedException \Spiral\Router\Exception\RouteException
     */
    public function testPipelineException()
    {
        $r = (new Route('/<controller>[/<action>[/<id>]]', new Group([
            'test' => TestController::class
        ])))->withMiddleware([new HeaderMiddleware(), HeaderMiddleware::class]);

        $r = $r->match(new ServerRequest([], [], new Uri('/test')));
        $response = $r->handle(new ServerRequest([], [], new Uri('/test')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());
        $this->assertSame("Value*,Value*", $response->getHeaderLine('Header'));
    }

    /**
     * @expectedException \Spiral\Router\Exception\RouteException
     */
    public function testPipelineExceptionMiddleware()
    {
        $r = (new Route('/<controller>[/<action>[/<id>]]', new Group([
            'test' => TestController::class
        ])))->withMiddleware([new HeaderMiddleware(), 'other']);

        $r = $r->withContainer($this->container);

        $r = $r->match(new ServerRequest([], [], new Uri('/test')));
        $r->handle(new ServerRequest([], [], new Uri('/test')));
    }
}

class HeaderMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        return $handler->handle($request)->withAddedHeader("Header", "Value*");
    }
}