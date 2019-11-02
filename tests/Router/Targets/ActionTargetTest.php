<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router\Tests\Targets;

use PHPUnit\Framework\TestCase;
use Spiral\Router\Autofill;
use Spiral\Router\Route;
use Spiral\Router\Target\Action;
use Spiral\Router\Tests\Diactoros\UriFactory;
use Spiral\Router\Tests\Fixtures\TestController;
use Spiral\Router\UriHandler;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class ActionTargetTest extends TestCase
{
    public function testDefaultAction(): void
    {
        $route = new Route('/home', new Action(TestController::class, 'test'));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $this->assertSame(['action' => 'test'], $route->getDefaults());
    }

    public function testConstrains(): void
    {
        $route = new Route('/home', new Action(TestController::class, 'test'));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $this->assertEquals(['action' => new Autofill('test')], $route->getUriHandler()->getConstrains());

        $route = new Route('/<action>', new Action(TestController::class, ['test', 'other']));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $this->assertSame(['action' => ['test', 'other']], $route->getUriHandler()->getConstrains());
    }

    /**
     * @expectedException \Spiral\Router\Exception\ConstrainException
     */
    public function testConstrainedAction(): void
    {
        $route = new Route('/home', new Action(TestController::class, ['test', 'other']));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route->match(new ServerRequest());
    }

    public function testMatch(): void
    {
        $route = new Route(
            '/test[/<action>]',
            new Action(TestController::class, ['test', 'other'])
        );
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route = $route->withDefaults(['action' => 'test']);

        $this->assertNull($route->match(new ServerRequest()));
        $this->assertNull($route->match(new ServerRequest([], [], new Uri('/test/something'))));
        $this->assertNull($route->match(new ServerRequest([], [], new Uri('/test/tester'))));

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test')))
        );

        $this->assertSame(['action' => 'test'], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test/')))
        );
        $this->assertSame(['action' => 'test'], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test/test')))
        );
        $this->assertSame(['action' => 'test'], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test/test/')))
        );
        $this->assertSame(['action' => 'test'], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test/other')))
        );

        $this->assertSame(['action' => 'other'], $match->getMatches());
    }

    /**
     * @expectedException \Spiral\Router\Exception\InvalidArgumentException
     */
    public function testActionException(): void
    {
        new Action(TestController::class, $this);
    }
}
