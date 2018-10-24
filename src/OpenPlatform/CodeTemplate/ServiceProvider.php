<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/23
 * Time: 10:54
 */

namespace Wise\OpenPlatform\CodeTemplate;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['code_template'] = function ($app) {
            return new Client($app);
        };
    }

}