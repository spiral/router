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
use Spiral\Router\Route;
use Spiral\Router\Target\Controller;
use Spiral\Router\Tests\Diactoros\UriFactory;
use Spiral\Router\Tests\Fixtures\TestController;
use Spiral\Router\UriHandler;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class ControllerTargetTest extends TestCase
{
    public function testDefaultAction(): void
    {
        $route = new Route('/home[/<action>]', new Controller(TestController::class));
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $this->assertSame(['action' => null], $route->getDefaults());
    }

    public function testMatch(): void
    {
        $route = new Route(
            '/test[/<action>]',
            new Controller(TestController::class)
        );
        $route = $route->withUriHandler(new UriHandler(new UriFactory()));

        $this->assertNull($route->match(new ServerRequest()));
        $this->assertNotNull($route->match(new ServerRequest([], [], new Uri('/test/something'))));
        $this->assertNotNull($route->match(new ServerRequest([], [], new Uri('/test/tester'))));

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test')))
        );

        $this->assertSame(['action' => null], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test/')))
        );
        $this->assertSame(['action' => null], $match->getMatches());

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
}
