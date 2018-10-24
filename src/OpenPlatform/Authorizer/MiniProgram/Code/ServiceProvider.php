<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/24
 * Time: 10:19
 */

namespace Wise\OpenPlatform\Authorizer\MiniProgram\Code;


use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{

    public function register(Container $app)
    {
        $app['code'] = function ($app) {
            return new Client($app);
        };
    }
}