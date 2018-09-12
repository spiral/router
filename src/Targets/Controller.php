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
 * new Controller(HomeController::class, 'index'); // default action
 */
final class Controller extends Action
{
    /**
     * @param string      $controller
     * @param string|null $defaultAction
     */
    public function __construct(string $controller, string $defaultAction = null)
    {
        parent::__construct($controller, []);
        if (!empty($defaultAction)) {
            $this->setDefaults(['action' => $defaultAction]);
        }
    }
}