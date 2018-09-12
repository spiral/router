<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Routing;

use Spiral\Core\CoreInterface;
use Spiral\Routing\Traits\PipelineTrait;

/**
 * CoreRoute provides ability to invoke controller action(s) or group of controllers.
 *
 * new CoreRoute('/[<controller>[/<action>[/<id>]]]', new Group('App\Controllers'));
 * new CoreRoute('/home[/<action>[/<id>]]', new Controller(App\Controllers\HomeController::class));
 * new CoreRoute('/login', new Action(App\Controllers\SignUpController::class, 'login'));
 */
abstract class CoreRoute extends AbstractRoute
{
    use PipelineTrait;

    /** @var TargetInterface */
    private $target;

    /** @var CoreInterface */
    private $core;

    /**
     * @param string          $pattern  Uri pattern.
     * @param TargetInterface $target   Defines set of controllers (statically or dynamically) route can invoke.
     * @param array           $defaults Default value set.
     */
    public function __construct(string $pattern, TargetInterface $target, array $defaults = [])
    {
        parent::__construct($pattern, array_merge($defaults, $target->getDefaults()));
        $this->target = $target;
    }
}