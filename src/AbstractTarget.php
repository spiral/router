<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router;

use Doctrine\Common\Inflector\Inflector;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Spiral\Core\CoreInterface;
use Spiral\Router\Exceptions\TargetException;

abstract class AbstractTarget implements TargetInterface
{
    // Automatically prepend HTTP verb to all action names.
    const RESTFUL = 1;

    /** @var array */
    private $defaults = [];

    /** @var array */
    private $constrains = [];

    /** @var CoreInterface */
    private $core;

    /** @var CoreHandler */
    private $handler;

    /** @var bool */
    private $verbActions;

    /**
     * @param array $defaults
     * @param array $constrains
     * @param int   $options
     */
    public function __construct(array $defaults, array $constrains, int $options = 0)
    {
        $this->defaults = $defaults;
        $this->constrains = $constrains;
        $this->verbActions = $options & self::RESTFUL;
    }

    /**
     * @inheritdoc
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * @inheritdoc
     */
    public function getConstrains(): array
    {
        return $this->constrains;
    }

    /**
     * @param CoreInterface $core
     *
     * @return TargetInterface|$this
     */
    public function withCore(CoreInterface $core): TargetInterface
    {
        $target = clone $this;
        $this->core = $core;
        $this->handler = null;

        return $target;
    }

    /**
     * @inheritdoc
     */
    public function getHandler(ContainerInterface $container, array $matches): Handler
    {
        $action = $this->resolveAction($matches);

        return $this->coreHandler($container)->withContext(
            $this->resolveController($matches),
            !empty($action) ? Inflector::camelize($action) : null,
            $matches
        )->withVerbActions($this->verbActions);
    }

    /**
     * @param ContainerInterface $container
     *
     * @return CoreHandler
     */
    protected function coreHandler(ContainerInterface $container): CoreHandler
    {
        if (!empty($this->handler)) {
            return $this->handler;
        }

        try {
            $this->handler = new CoreHandler(
                $this->core ?? $container->get(CoreInterface::class),
                $container->get(ResponseFactoryInterface::class)
            );

            return $this->handler;
        } catch (ContainerExceptionInterface $e) {
            throw new TargetException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Return controller class name.
     *
     * @param array $matches
     *
     * @return string
     *
     * @throws TargetException
     */
    abstract protected function resolveController(array $matches): string;

    /**
     * Return target controller action.
     *
     * @param array $matches
     *
     * @return string
     *
     * @throws TargetException
     */
    abstract protected function resolveAction(array $matches): ?string;
}