<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests\Fixtures;

use Spiral\Core\Controller;

class OtherController extends Controller
{
    public function actionAction()
    {
        return "action!";
    }
}
