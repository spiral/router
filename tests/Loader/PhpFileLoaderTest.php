<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Loader;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriFactoryInterface;
use Spiral\Core\Container;
use Spiral\Router\Exception\LoaderLoadException;
use Spiral\Router\Loader\LoaderInterface;
use Spiral\Router\Loader\PhpFileLoader;
use Spiral\Router\RouteCollection;
use Spiral\Router\RouterInterface;

final class PhpFileLoaderTest extends TestCase
{
    private Container $container;

    public function testLoad(): void
    {
        $loader = new PhpFileLoader($this->container, $this->container);

        $routes = $loader->load(\dirname(__DIR__) . '/Fixtures/file.php');
        self::assertInstanceOf(RouteCollection::class, $routes);
        self::assertCount(3, $routes);

        $this->expectException(LoaderLoadException::class);
        $loader->load(\dirname(__DIR__) . '/Fixtures/unknown.php');
    }

    public function testSupports(): void
    {
        $loader = new PhpFileLoader($this->container, $this->container);

        self::assertTrue($loader->supports('file.php'));
        self::assertTrue($loader->supports('file.php', 'php'));

        self::assertFalse($loader->supports('file.php', 'txt'));
        self::assertFalse($loader->supports('file.txt'));
        self::assertFalse($loader->supports('file.txt', 'txt'));
    }

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->bind(LoaderInterface::class, $this->createMock(LoaderInterface::class));
        $this->container->bind(RouterInterface::class, $this->createMock(RouterInterface::class));
        $this->container->bind(UriFactoryInterface::class, $this->createMock(UriFactoryInterface::class));
    }
}
