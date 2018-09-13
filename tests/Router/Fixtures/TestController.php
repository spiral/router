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
    protected $defaultAction = 'test';

    public function testAction()
    {
        return "hello world";
    }

    public function idAction(string $id)
    {
        return $id;
    }

    public function echoAction()
    {
        echo "echoed";
    }
}