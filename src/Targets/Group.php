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
 * Provides ability to invoke from a given controller set:
 *
 * Example: new Group(['signup' => SignUpController::class]);
 */
final class Group extends AbstractTarget
{
    /** @var array */
    private $controllers;

    /**
     * @param array $controllers
     * @param int   $options
     */
    public function __construct(array $controllers, int $options = 0)
    {
        $this->controllers = $controllers;
        parent::__construct([], ['controller' => array_keys($controllers), 'action' => null], $options);
    }

    /**
     * @inheritdoc
     */
    protected function resolveController(array $matches): string
    {
        if (empty($matches['controller'])) {
            throw new TargetException("Unable to evaluate target, no controller is given.");
        }

        if (!isset($this->controllers[$matches['controller']])) {
            throw new TargetException(sprintf("Invalid controller `%s`.", $matches['controller']));
        }

        return $this->controllers[$matches['controller']];
    }

    /**
     * @inheritdoc
     */
    protected function resolveAction(array $matches): ?string
    {
        return $matches['action'];
    }
}