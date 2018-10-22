<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/22
 * Time: 9:09
 */

namespace Wise\OpenPlatform\Base;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{

    public function register(Container $pimple)
    {
        $app['base'] = function ($app) {
            return new Client($app);
        };
    }
}