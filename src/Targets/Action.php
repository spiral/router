<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Targets;

use Spiral\Router\AbstractTarget;
use Spiral\Router\Exceptions\InvalidArgumentException;
use Spiral\Router\Exceptions\TargetException;

/**
 * Targets to specific controller action or actions.
 *
 * Examples:
 *
 * new Action(HomeController::class, "index");
 * new Action(SingUpController::class, ["login", "logout"]); // creates <action> constrain
 */
final class Action extends AbstractTarget
{
    /** @var string */
    private $controller;

    /** @var array|string */
    private $action;

    /**
     * Action constructor.
     *
     * @param string       $controller Controller class name.
     * @param string|array $action     One or multiple allowed actions.
     * @param int          $options    Action behaviour options, see OPTION_ constants.
     */
    public function __construct(string $controller, $action, int $options = 0)
    {
        if (!is_string($action) && !is_array($action)) {
            throw new InvalidArgumentException(sprintf(
                "Action parameter must type string or array, `%s` given.",
                gettype($action)
            ));
        }

        $this->controller = $controller;
        $this->action = $action;

        if (is_string($action)) {
            parent::__construct(compact('action'), [], $options);
        } else {
            parent::__construct([], compact('action'), $options);
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
     * @inheritdoc
     */
    protected function resolveAction(array $matches): string
    {
        $action = $this->action;
        if (is_string($action)) {
            return $action;
        }

        if (empty($matches['action']) || (!empty($action) && !in_array($matches['action'], $action))) {
            throw new TargetException("Invalid action target, action not allowed.");
        }

        return $matches['action'];
    }
}