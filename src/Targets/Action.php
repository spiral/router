<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Targets;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
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
     */
    public function __construct(string $controller, $action)
    {
        if (!class_exists($controller)) {
            throw new TargetException("Undefined class `{$controller}`");
        }

        if (!is_string($action) && !is_array($action)) {
            throw new InvalidArgumentException(sprintf(
                "Action parameter must type string or array, `%s` given.",
                gettype($action)
            ));
        }

        $this->controller = $controller;
        $this->action = $action;

        if (is_string($action)) {
            parent::__construct(compact('action'), []);
        } else {
            parent::__construct([], compact('action'));
        }
    }

    /**
     * @inheritdoc
     */
    public function makeHandler(ContainerInterface $container, array $matches): Handler
    {
        $action = $this->action;
        if (!is_string($action)) {
            if (empty($matches['action']) || !in_array($matches['action'], $action)) {
                throw new TargetException("Invalid action target.");
            }

            $action = $matches['action'];
        }

        return $this->coreHandler($container)->withContext($this->controller, $action, $matches);
    }
}