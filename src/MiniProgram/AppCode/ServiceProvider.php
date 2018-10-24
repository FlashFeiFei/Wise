<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/24
 * Time: 11:25
 */

namespace Wise\MiniProgram\AppCode;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{

    public function register(Container $app)
    {
        $app['app_code'] = function ($app) {
            return new Client($app);
        };
    }
}