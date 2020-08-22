<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router\Targets;

use PHPUnit\Framework\TestCase;
use Spiral\Router\Exception\ConstrainException;
use Spiral\Router\Route;
use Spiral\Router\Target\Namespaced;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Router\UriHandler;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;

class NamespacedTargetTest extends TestCase
{
    public function testDefaultAction(): void
    {
        $route = new Route('/<controller>/<action>', new Namespaced('Spiral\Router\Fixtures'));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $this->assertSame(['controller' => null, 'action' => null], $route->getDefaults());
    }

    public function testConstrainedController(): void
    {
        $this->expectException(ConstrainException::class);

        $route = new Route('/<action>', new Namespaced('Spiral\Router\Fixtures'));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route->match(new ServerRequest());
    }

    public function testConstrainedAction(): void
    {
        $this->expectException(ConstrainException::class);

        $route = new Route('/<controller>', new Namespaced('Spiral\Router\Fixtures'));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route->match(new ServerRequest());
    }

    public function testMatch(): void
    {
        $route = new Route(
            '/<controller>[/<action>]',
            new Namespaced('Spiral\Router\Fixtures')
        );
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route = $route->withDefaults(['controller' => 'test']);

        $this->assertNull($route->match(new ServerRequest()));

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test')))
        );

        $this->assertSame(['controller' => 'test', 'action' => null], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test/action/')))
        );

        $this->assertSame(['controller' => 'test', 'action' => 'action'], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/other/action/')))
        );

        $this->assertSame(['controller' => 'other', 'action' => 'action'], $match->getMatches());
    }

    /**
     * @dataProvider defaultProvider
     *
     * @param string $pattern
     * @param string $uri
     * @param array $defaults
     */
    public function testDefaults(string $pattern, string $uri, array $defaults): void
    {
        $route = new Route($pattern, new Namespaced('Spiral\Router\Fixtures'), $defaults);
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $request = new ServerRequest([], [], new Uri($uri));

        $match = $route->match($request);
        $this->assertNotNull($match);

        $values = $match->getMatches();
        $this->assertNotNull($values['controller']);
        $this->assertNotNull($values['action']);
    }

    public function defaultProvider(): array
    {
        return [
            ['<controller>[/<action>]', '/home', ['controller' => 'home', 'action' => 'test']],
            ['<controller>[/<action>]', '/home/test', ['controller' => 'home', 'action' => 'test']],
            ['/<controller>[/<action>]', '/home', ['controller' => 'home', 'action' => 'test']],
            ['/<controller>[/<action>]', '/home/test', ['controller' => 'home', 'action' => 'test']],

            ['[<controller>[/<action>]]', '/home', ['controller' => 'home', 'action' => 'test']],
            ['[<controller>[/<action>]]', '/home/test', ['controller' => 'home', 'action' => 'test']],
            ['[<controller>[/<action>]]', '/', ['controller' => 'home', 'action' => 'test']],
            ['[<controller>[/<action>]]', '', ['controller' => 'home', 'action' => 'test']],

            ['[/<controller>[/<action>]]', '/home', ['controller' => 'home', 'action' => 'test']],
            ['[/<controller>[/<action>]]', '/home/test', ['controller' => 'home', 'action' => 'test']],
            ['[/<controller>[/<action>]]', '/', ['controller' => 'home', 'action' => 'test']],
            ['[/<controller>[/<action>]]', '', ['controller' => 'home', 'action' => 'test']],
        ];
    }
}
