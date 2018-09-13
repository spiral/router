<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests\Fixtures;

use Spiral\Core\Controller;

class TestController extends Controller
{
    public function testAction()
    {
        return "hello world";
    }
}