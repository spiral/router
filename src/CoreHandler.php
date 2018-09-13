<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exceptions\ControllerException;
use Spiral\Http\Exceptions\ClientException;
use Spiral\Http\Exceptions\ClientExceptions\BadRequestException;
use Spiral\Http\Exceptions\ClientExceptions\ForbiddenException;
use Spiral\Http\Exceptions\ClientExceptions\NotFoundException;
use Spiral\Http\Traits\JsonTrait;
use Spiral\Router\Exceptions\HandlerException;

class CoreHandler implements RequestHandlerInterface
{
    use JsonTrait;

    /** @var CoreInterface */
    private $core;

    /** @var string|null */
    private $controller;

    /** @var string|null */
    private $action;

    /** @var bool */
    private $verbActions;

    /** @var array|null */
    private $parameters;

    /** @var ResponseFactoryInterface */
    private $responseFactory;

    /**
     * @param CoreInterface            $core
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(CoreInterface $core, ResponseFactoryInterface $responseFactory)
    {
        $this->core = $core;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param string $controller
     * @param string $action
     * @param array  $parameters
     *
     * @return CoreHandler
     */
    public function withContext(string $controller, string $action, array $parameters): CoreHandler
    {
        $handler = clone $this;
        $handler->controller = $controller;
        $handler->action = $action;
        $handler->parameters = $parameters;

        return $handler;
    }

    /**
     * Disable or enable HTTP prefix for actions.
     *
     * @param bool $verbActions
     * @return CoreHandler
     */
    public function withVerbActions(bool $verbActions): CoreHandler
    {
        $handler = clone $this;
        $handler->verbActions = $verbActions;

        return $handler;
    }

    /**
     * @inheritdoc
     */
    public function handle(Request $request): Response
    {
        if ($this->controller === null) {
            throw new HandlerException("Controller and action pair is not set.");
        }

        $outputLevel = ob_get_level();
        ob_start();

        $output = $result = null;

        $response = $this->responseFactory->createResponse(200);
        try {
            $result = $this->core->callAction(
                $this->controller,
                $this->getAction($request),
                $this->parameters,
                [Request::class => $request, Response::class => $response]
            );
        } catch (ControllerException $e) {
            throw $this->mapException($e);
        } catch (\Throwable $e) {
            ob_get_clean();
            throw $e;
        } finally {
            while (ob_get_level() > $outputLevel + 1) {
                $output = ob_get_clean() . $output;
            }
        }

        return $this->wrapResponse(
            $response,
            $result,
            ob_get_clean() . $output
        );
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getAction(Request $request): string
    {
        if ($this->verbActions) {
            return strtolower($request->getMethod()) . ucfirst($this->action);
        }

        return $this->action;
    }

    /**
     * Convert endpoint result into valid response.
     *
     * @param Response $response Initial pipeline response.
     * @param mixed    $result   Generated endpoint output.
     * @param string   $output   Buffer output.
     *
     * @return Response
     */
    private function wrapResponse(Response $response, $result = null, string $output = ''): Response
    {
        if ($result instanceof Response) {
            if (!empty($output) && $result->getBody()->isWritable()) {
                $result->getBody()->write($output);
            }

            return $result;
        }

        if (is_array($result) || $result instanceof \JsonSerializable) {
            $response = $this->writeJson($response, $result);
        } else {
            $response->getBody()->write($result);
        }

        //Always glue buffered output
        $response->getBody()->write($output);

        return $response;
    }

    /**
     * Converts core specific ControllerException into HTTP ClientException.
     *
     * @param ControllerException $exception
     *
     * @return ClientException
     */
    private function mapException(ControllerException $exception): ClientException
    {
        switch ($exception->getCode()) {
            case ControllerException::BAD_ACTION:
                //no break
            case ControllerException::NOT_FOUND:
                return new NotFoundException($exception->getMessage());
            case ControllerException::FORBIDDEN:
                return new ForbiddenException($exception->getMessage());
            default:
                return new BadRequestException($exception->getMessage());
        }
    }
}