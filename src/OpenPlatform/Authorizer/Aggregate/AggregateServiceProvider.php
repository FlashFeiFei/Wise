<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/23
 * Time: 16:14
 */

namespace Wise\OpenPlatform\Authorizer\Aggregate;

use Wise\OpenPlatform\Authorizer\Aggregate\Account\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AggregateServiceProvider implements ServiceProviderInterface
{

    public function register(Container $app)
    {
        !isset($app['account']) && $app['account'] = function ($app) {
            return new Client($app);
        };
    }
}