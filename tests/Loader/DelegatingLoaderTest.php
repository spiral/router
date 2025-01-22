<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Loader;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Router\Loader\DelegatingLoader;
use Spiral\Router\Loader\LoaderRegistry;
use Spiral\Router\Loader\PhpFileLoader;
use Spiral\Router\RouteCollection;
use Spiral\Tests\Router\Stub\TestLoader;

final class DelegatingLoaderTest extends TestCase
{
    public function testLoad(): void
    {
        $loader = new DelegatingLoader(new LoaderRegistry([
            new TestLoader()
        ]));

        $this->assertInstanceOf(RouteCollection::class, $loader->load('file.yaml', 'yaml'));
    }

    public function testSupports(): void
    {
        $loader = new DelegatingLoader(new LoaderRegistry());

        $this->assertFalse($loader->supports('file.php'));
        $this->assertFalse($loader->supports('file.php', 'php'));

        $container = new Container();

        $loader = new DelegatingLoader(new LoaderRegistry([new PhpFileLoader($container, $container)]));
        $this->assertTrue($loader->supports('file.php'));
        $this->assertTrue($loader->supports('file.php', 'php'));
        $this->assertFalse($loader->supports('file.php', 'yaml'));
        $this->assertFalse($loader->supports('file.yaml'));
        $this->assertFalse($loader->supports('file.yaml', 'yaml'));

        $loader = new DelegatingLoader(new LoaderRegistry([
            new PhpFileLoader($container, $container),
            new TestLoader()
        ]));
        $this->assertTrue($loader->supports('file.php'));
        $this->assertTrue($loader->supports('file.php', 'php'));
        $this->assertFalse($loader->supports('file.yaml'));
        $this->assertTrue($loader->supports('file.yaml', 'yaml'));
        $this->assertFalse($loader->supports('file.yaml', 'php'));
    }
}
