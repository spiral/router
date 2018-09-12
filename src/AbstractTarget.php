<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Spiral\Core\CoreInterface;
use Spiral\Routing\Exceptions\TargetException;

abstract class AbstractTarget implements TargetInterface
{
    /** @var array */
    private $defaults = [];

    /** @var array */
    private $constrains = [];

    /** @var CoreInterface */
    private $core;

    /** @var CoreHandler */
    private $handler;

    /**
     * @param array $defaults
     * @param array $constrains
     */
    public function __construct(array $defaults, array $constrains)
    {
        $this->defaults = $defaults;
        $this->constrains = $constrains;
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
}