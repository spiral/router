<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Router\Target;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Spiral\Core\CoreInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Router\CoreHandler;
use Spiral\Router\Exception\TargetException;
use Spiral\Router\TargetInterface;

abstract class AbstractTarget implements TargetInterface
{
    // Automatically prepend HTTP verb to all action names.
    public const RESTFUL = 1;

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

    private $defaultAction;

    /**
     * @param array  $defaults
     * @param array  $constrains
     * @param int    $options
     * @param string $defaultAction
     */
    public function __construct(array $defaults, array $constrains, int $options = 0, string $defaultAction = 'index')
    {
        $this->defaults = $defaults;
        $this->constrains = $constrains;
        $this->verbActions = ($options & self::RESTFUL) === self::RESTFUL;
        $this->defaultAction = $defaultAction;
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
     * @return TargetInterface|$this
     */
    public function withCore(CoreInterface $core): TargetInterface
    {
        $target = clone $this;
        $target->core = $core;
        $target->handler = null;

        return $target;
    }

    /**
     * @inheritdoc
     */
    public function getHandler(ContainerInterface $container, array $matches): Handler
    {
        return $this->coreHandler($container)->withContext(
            $this->resolveController($matches),
            $this->resolveAction($matches) ?? $this->defaultAction,
            $matches
        )->withVerbActions($this->verbActions);
    }

    /**
     * @param ContainerInterface $container
     * @return CoreHandler
     */
    protected function coreHandler(ContainerInterface $container): CoreHandler
    {
        if ($this->handler !== null) {
            return $this->handler;
        }

        try {
            // construct on demand
            $this->handler = new CoreHandler(
                $this->core ?? $container->get(CoreInterface::class),
                $container->get(ScopeInterface::class),
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
     * @return string
     *
     * @throws TargetException
     */
    abstract protected function resolveController(array $matches): string;

    /**
     * Return target controller action.
     *
     * @param array $matches
     * @return string|null
     *
     * @throws TargetException
     */
    abstract protected function resolveAction(array $matches): ?string;
}
