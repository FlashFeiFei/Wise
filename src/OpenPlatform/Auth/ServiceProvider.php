<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/19
 * Time: 14:47
 */

namespace Wise\OpenPlatform\Auth;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider  implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $app['verify_ticket'] = function ($app) {
            return new VerifyTicket($app);
        };

        $app['access_token'] = function ($app) {
            return new AccessToken($app);
        };
    }

}