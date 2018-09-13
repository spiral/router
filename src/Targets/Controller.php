<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Targets;

use Spiral\Router\AbstractTarget;
use Spiral\Router\Exceptions\TargetException;

/**
 * Targets to all actions in specific controller. Variation of Action without action constrain.
 *
 * Examples:
 * new Controller(HomeController::class);
 * new Controller(HomeController::class, 'index'); // default action
 */
final class Controller extends AbstractTarget
{
    /** @var string */
    private $controller;

    /**
     * @param string      $controller
     * @param string|null $defaultAction
     * @param int         $options
     */
    public function __construct(string $controller, string $defaultAction = null, int $options = 0)
    {
        // always constrained to have action parameter
        parent::__construct([], ['action' => null]);

        if (!empty($defaultAction)) {
            $this->setDefaults(['action' => $defaultAction]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function resolveController(array $matches): string
    {
        return $this->controller;
    }

    /**
     * @param array $matches
     * @return string
     */
    protected function resolveAction(array $matches): string
    {
        if (empty($matches['action'])) {
            throw new TargetException("Invalid action target, action is not set.");
        }

        return $matches['action'];
    }
}