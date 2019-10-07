<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Router\Tests;

class Call
{
    public function __invoke()
    {
        return 'invoked';
    }
}
