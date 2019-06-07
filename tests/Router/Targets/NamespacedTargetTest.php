<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests\Targets;

use PHPUnit\Framework\TestCase;
use Spiral\Router\Route;
use Spiral\Router\Target\Namespaced;
use Spiral\Router\Tests\Diactoros\UriFactory;
use Spiral\Router\UriHandler;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class NamespacedTargetTest extends TestCase
{
    public function testDefaultAction()
    {
        $route = new Route("/<controller>/<action>", new Namespaced('Spiral\Router\Fixtures'));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $this->assertSame(['controller' => null, 'action' => null], $route->getDefaults());
    }

    /**
     * @expectedException \Spiral\Router\Exception\ConstrainException
     */
    public function testConstrainedController()
    {
        $route = new Route("/<action>", new Namespaced('Spiral\Router\Fixtures'));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route->match(new ServerRequest());
    }

    /**
     * @expectedException \Spiral\Router\Exception\ConstrainException
     */
    public function testConstrainedAction()
    {
        $route = new Route("/<controller>", new Namespaced('Spiral\Router\Fixtures'));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route->match(new ServerRequest());
    }

    public function testMatch()
    {
        $route = new Route(
            "/<controller>[/<action>]",
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
     * @param array  $defaults
     */
    public function testDefaults(string $pattern, string $uri, array $defaults)
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