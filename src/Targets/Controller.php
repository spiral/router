<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Targets;

use Spiral\Router\AbstractTarget;

/**
 * Targets to all actions in specific controller. Variation of Action without action constrain.
 *
 * Example: new Controller(HomeController::class);
 */
final class Controller extends AbstractTarget
{
    /** @var string */
    private $controller;

    /**
     * @param string $controller
     * @param int    $options
     */
    public function __construct(string $controller, int $options = 0)
    {
        parent::__construct(['action' => null], ['action' => null], $options);
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
    protected function resolveAction(array $matches): ?string
    {
        return $matches['action'];
    }
}