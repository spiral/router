<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Http\Exception\ClientException\BadRequestException;
use Spiral\Http\Exception\ClientException\ForbiddenException;
use Spiral\Http\Exception\ClientException\NotFoundException;
use Spiral\Router\CoreHandler;
use Spiral\Router\Exception\HandlerException;
use Spiral\Router\Exception\TargetException;
use Spiral\Router\Target\Action;
use Spiral\Tests\Router\Fixtures\TestController;
use Laminas\Diactoros\ServerRequest;

class CoreTest extends BaseTest
{
    public function testMissingBinding(): void
    {
        $this->expectException(TargetException::class);

        $action = new Action(TestController::class, 'test');

        $container = new Container();
        $action->getHandler($container, []);
    }

    public function testAutoCore(): void
    {
        $action = new Action(TestController::class, 'test');
        $handler = $action->getHandler($this->container, []);

        $this->assertInstanceOf(CoreHandler::class, $handler);
    }

    public function testWithAutoCore(): void
    {
        $action = new Action(TestController::class, 'test');

        $action = $action->withCore(new TestCore($this->container->get(CoreInterface::class)));

        $handler = $action->getHandler($this->container, []);
        $this->assertInstanceOf(CoreHandler::class, $handler);

        $result = $handler->handle(new ServerRequest());

        $this->assertSame('@wrapped.hello world', (string)$result->getBody());
    }

    public function testErrAction(): void
    {
        $this->expectExceptionMessage('error.controller');
        $this->expectException(\Error::class);

        $action = new Action(TestController::class, 'err');

        $action = $action->withCore(new TestCore($this->container->get(CoreInterface::class)));

        $handler = $action->getHandler($this->container, []);
        $this->assertInstanceOf(CoreHandler::class, $handler);

        $handler->handle(new ServerRequest());
    }

    public function testRSP(): void
    {
        $action = new Action(TestController::class, 'rsp');

        $handler = $action->getHandler($this->container, []);
        $this->assertInstanceOf(CoreHandler::class, $handler);

        $result = $handler->handle(new ServerRequest());

        $this->assertSame('rspbuf', (string)$result->getBody());
    }

    public function testJson(): void
    {
        $action = new Action(TestController::class, 'json');

        $handler = $action->getHandler($this->container, []);
        $this->assertInstanceOf(CoreHandler::class, $handler);

        $result = $handler->handle(new ServerRequest());

        $this->assertSame(301, $result->getStatusCode());
        $this->assertSame('{"status":301,"msg":"redirect"}', (string)$result->getBody());
    }

    public function testForbidden(): void
    {
        $this->expectException(ForbiddenException::class);

        $action = new Action(TestController::class, 'forbidden');
        $r = $action->getHandler($this->container, [])->handle(new ServerRequest());
    }

    public function testNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $action = new Action(TestController::class, 'not-found');
        $r = $action->getHandler($this->container, [])->handle(new ServerRequest());
    }

    public function testBadRequest(): void
    {
        $this->expectException(BadRequestException::class);

        $action = new Action(TestController::class, 'weird');
        $r = $action->getHandler($this->container, [])->handle(new ServerRequest());
    }

    public function testCoreException(): void
    {
        $this->expectException(HandlerException::class);

        /** @var CoreHandler $core */
        $core = $this->container->get(CoreHandler::class);
        $core->handle(new ServerRequest());
    }

    public function testRESTFul(): void
    {
        $action = new Action(TestController::class, 'Target', Action::RESTFUL);
        $r = $action->getHandler($this->container, [])->handle(new ServerRequest(
            [],
            [],
            '',
            'POST'
        ))
        ;

        $this->assertSame('POST', (string)$r->getBody());

        $r = $action->getHandler($this->container, [])->handle(new ServerRequest(
            [],
            [],
            '',
            'DELETE'
        ))
        ;

        $this->assertSame('DELETE', (string)$r->getBody());
    }
}
