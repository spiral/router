<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Targets;

/**
 * Targets to all actions in specific controller. Variation of Action without action constrain.
 *
 * Examples:
 * new Controller(HomeController::class);
 */
final class Controller extends Action
{
    /**
     * @param string $controller
     */
    public function __construct(string $controller)
    {
        parent::__construct($controller, []);
    }
}