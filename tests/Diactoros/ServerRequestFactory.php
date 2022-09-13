<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Diactoros;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Nyholm\Psr7\ServerRequest;

final class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * @param string              $method
     * @param UriInterface|string $uri
     * @param array               $serverParams
     * @return ServerRequestInterface
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest($method, $uri, serverParams: $serverParams);
    }
}
