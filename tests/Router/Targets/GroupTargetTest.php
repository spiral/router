<?php

declare(strict_types=1);

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests\Targets;

use PHPUnit\Framework\TestCase;
use Spiral\Router\Route;
use Spiral\Router\Target\Group;
use Spiral\Router\Tests\Diactoros\UriFactory;
use Spiral\Router\Tests\Fixtures\TestController;
use Spiral\Router\UriHandler;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class GroupTargetTest extends TestCase
{
    public function testDefaultAction(): void
    {
        $route = new Route('/<controller>/<action>', new Group(['test' => TestController::class]));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $this->assertSame(['controller' => null, 'action' => null], $route->getDefaults());
    }

    /**
     * @expectedException \Spiral\Router\Exception\ConstrainException
     */
    public function testConstrainedController(): void
    {
        $route = new Route('/<action>', new Group(['test' => TestController::class]));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route->match(new ServerRequest());
    }

    /**
     * @expectedException \Spiral\Router\Exception\ConstrainException
     */
    public function testConstrainedAction(): void
    {
        $route = new Route('/<controller>', new Group(['test' => TestController::class]));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));
        $route->match(new ServerRequest());
    }

    public function testMatch(): void
    {
        $route = new Route(
            '/<controller>[/<action>]',
            new Group(['test' => TestController::class])
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

        $this->assertNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/other/action/')))
        );

        $this->assertNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/other')))
        );
    }
}
