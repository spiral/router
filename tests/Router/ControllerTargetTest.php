<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router;

use PHPUnit\Framework\TestCase;
use Spiral\Http\Uri;
use Spiral\Router\Fixtures\TestController;
use Spiral\Router\Targets\Controller;
use Zend\Diactoros\ServerRequest;

class ControllerTargetTest extends TestCase
{
    public function testDefaultAction()
    {
        $route = new Route("/home[/<action>]", new Controller(TestController::class));
        $this->assertSame(['action' => null], $route->getDefaults());
    }

    public function testMatch()
    {
        $route = new Route(
            "/test[/<action>]",
            new Controller(TestController::class)
        );

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