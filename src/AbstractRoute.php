<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;

class AbstractRoute implements RouteInterface
{
    /** @var string */
    private $name = '';

    public function withName(string $name): RouteInterface
    {
        // TODO: Implement withName() method.
    }

    public function getName(): string
    {
        // TODO: Implement getName() method.
    }

    public function withPrefix(string $prefix): RouteInterface
    {
        // TODO: Implement withPrefix() method.
    }

    public function getPrefix(): string
    {
        // TODO: Implement getPrefix() method.
    }

    public function withDefaults(array $matches): RouteInterface
    {
        // TODO: Implement withDefaults() method.
    }

    public function getDefaults(): array
    {
        // TODO: Implement getDefaults() method.
    }

    public function match(Request $request): ?RouteInterface
    {
        // TODO: Implement match() method.
    }

    public function uri($parameters = []): UriInterface
    {
        // TODO: Implement uri() method.
    }
}