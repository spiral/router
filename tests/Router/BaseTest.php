<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Spiral\Core\AbstractCore;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;
use Zend\Diactoros\Response;

abstract class BaseTest extends TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = new Container();
        $this->container->bind(ResponseFactoryInterface::class, new ResponseFactory());
        $this->container->bind(CoreInterface::class, Core::class);
    }

    protected function makeRouter(string $basePath = ''): RouterInterface
    {
        return new Router($basePath, $this->container);
    }
}

class ResponseFactory implements ResponseFactoryInterface
{
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return (new Response('php://memory', $code, []))->withStatus($code, $reasonPhrase);
    }
}

class Core extends AbstractCore
{

}