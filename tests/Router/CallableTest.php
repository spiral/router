<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests;

use Spiral\Http\Uri;
use Spiral\Router\Route;
use Zend\Diactoros\ServerRequest;

class CallableTest extends BaseTest
{
    public function testRoute()
    {
        $router = $this->makeRouter();
        $router->addRoute(
            'action',
            new Route('/something', function () {
                return "hello world";
            })
        );

        $response = $router->handle(new ServerRequest([], [], new Uri('/something')));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("hello world", (string)$response->getBody());
    }
}