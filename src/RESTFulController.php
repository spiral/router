<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing;

use Spiral\Core\Controller;

/**
 * Disables action prefix in order to use HTTP verb defined actions like getPosts, putPost.
 */
class RESTFulController extends Controller
{
    const ACTION_POSTFIX = '';
}