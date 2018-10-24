<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/19
 * Time: 13:57
 */

namespace Wise\Kernel\Providers;

use Wise\Kernel\Config;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ConfigServiceProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $app)
    {
        $app['config'] = function ($app) {
            return new Config($app->getConfig());
        };
    }
}