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
use Spiral\Router\Target\Group;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Tests\Router\Fixtures\TestController;
use Spiral\Router\UriHandler;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;

class GroupTargetTest extends TestCase
{
    public function testDefaultAction(): void
    {
        $route = new Route('/<controller>/<action>', new Group(['test' => TestController::class]));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $this->assertSame(['controller' => null, 'action' => null], $route->getDefaults());
    }

    public function testConstrainedController(): void
    {
        $this->expectException(ConstrainException::class);

        $route = new Route('/<action>', new Group(['test' => TestController::class]));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $route->match(new ServerRequest());
    }

    public function testConstrainedAction(): void
    {
        $this->expectException(ConstrainException::class);

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
