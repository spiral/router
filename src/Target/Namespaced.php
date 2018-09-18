<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Target;

use Doctrine\Common\Inflector\Inflector;
use Spiral\Router\AbstractTarget;
use Spiral\Router\Exception\TargetException;

/**
 * Provides ability to invoke any controller from given namespace.
 *
 * Example: new Namespaced("App\Controllers");
 */
final class Namespaced extends AbstractTarget
{
    /** @var string */
    private $namespace;

    /** @var string */
    private $postfix;

    /**
     * @param string $namespace
     * @param string $postfix
     * @param int    $options
     */
    public function __construct(string $namespace, string $postfix = 'Controller', int $options = 0)
    {
        $this->namespace = rtrim($namespace, '\\');
        $this->postfix = ucfirst($postfix);

        parent::__construct(
            ['controller' => null, 'action' => null],
            ['controller' => null, 'action' => null],
            $options
        );
    }

    /**
     * @inheritdoc
     */
    protected function resolveController(array $matches): string
    {
        if (preg_match('/[^a-z_0-9\-]/i', $matches['controller'])) {
            throw new TargetException("Invalid namespace target, controller name not allowed.");
        }

        return sprintf(
            "%s\\%s%s",
            $this->namespace,
            Inflector::classify($matches['controller']),
            $this->postfix
        );
    }

    /**
     * @inheritdoc
     */
    protected function resolveAction(array $matches): ?string
    {
        return $matches['action'];
    }
}