<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Bootloader\Http\RouterBootloader;
use Spiral\Nyholm\Bootloader\NyholmBootloader;
use Spiral\Router\Route;
use Spiral\Router\RouterInterface;
use Spiral\Router\Target\Group;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Router\Fixtures\TestController;
use Spiral\Tests\Router\Fixtures\UserContextBootloader;
use Spiral\Tests\Router\Fixtures\UserContextController;
use Spiral\Tests\Router\Stub\IdentityScopedMiddleware;

class ContainerScopeTest extends BaseTestingCase
{
    public function defineBootloaders(): array
    {
        return [
            RouterBootloader::class,
            NyholmBootloader::class,
            UserContextBootloader::class,
        ];
    }

    #[TestScope('http')]
    public function testRunOpenScopeSameTwice(): void
    {
        $router = $this->getRouter();

        $router->setRoute(
            'group',
            (new Route('/<controller>[/<action>[/<id>]]', new Group([
                'test' => TestController::class,
            ])))->withMiddleware(IdentityScopedMiddleware::class),
        );

        $this->fakeHttp()->get('/test/scopes')->assertBodySame('http-request, idenity, http, root');
        $this->fakeHttp()->get('/test/scopes')->assertBodySame('http-request, idenity, http, root');
    }

    public function testServerRequestInRootService(): void
    {
        $router = $this->getRouter();

        $router->setRoute(
            'group',
            (new Route('/<controller>[/<action>]', new Group([
                'context' => UserContextController::class,
            ]))),
        );

        $this->fakeHttp()->get('/context/scope?context')->assertBodySame('OK');
        $this->fakeHttp()->get('/context/scope?context')->assertBodySame('OK');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unable to resolve UserContext, invalid request.');
        $this->fakeHttp()->get('/context/scope');
    }

    private function getRouter(): RouterInterface
    {
        return $this->getContainer()->get(RouterInterface::class);
    }
}
