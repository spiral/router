<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing;

use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Core\CoreInterface;
use Spiral\Http\Exceptions\RESTFulException;

class RESTFulCore implements CoreInterface
{
    /** @var CoreInterface */
    private $core;

    /**
     * RESTFulCore constructor.
     *
     * @param CoreInterface $core
     */
    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }

    /**
     * @inheritdoc
     *
     * @throws RESTFulException
     */
    public function callAction(
        string $controller,
        string $action = null,
        array $parameters = [],
        array $scope = []
    ) {
        if (empty($scope[Request::class]) || !$scope[Request::class] instanceof Request) {
            throw new RESTfulException(
                "RESTFul core can only work in a proper http core, Request class is missing or invalid"
            );
        }

        return $this->core->callAction(
            $controller,
            $this->defineAction($scope[Request::class], $parameters, $action),
            $parameters,
            $scope
        );
    }

    /**
     * Define action name based on a given request method.
     *
     * @param Request $request
     * @param array   $parameters
     * @param string  $action
     * @return string
     */
    protected function defineAction(Request $request, array $parameters, string $action = null): string
    {
        //methodAction [putPost, getPost]
        return strtolower($request->getMethod()) . ucfirst($action);
    }
}