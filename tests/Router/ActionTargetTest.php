<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\Router\Fixtures\TestController;
use Spiral\Router\Route;
use Spiral\Router\Targets\Action;
use Zend\Diactoros\ServerRequest;

class ActionTargetTest extends TestCase
{
    public function testDefaultAction()
    {
        $route = new Route("/home", new Action(TestController::class, "test"));
        $this->assertSame(['action' => 'test'], $route->getDefaults());
    }

    /**
     * @expectedException \Spiral\Router\Exceptions\ConstrainException
     */
    public function testConstrainedAction()
    {
        $route = new Route("/home", new Action(TestController::class, ["test", "other"]));
        $route->match(new ServerRequest());
    }
}