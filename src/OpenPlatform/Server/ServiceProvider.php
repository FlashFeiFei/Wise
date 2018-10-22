<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/22
 * Time: 10:49
 */

namespace Wise\OpenPlatform\Server;

use Wise\Kernel\Encryptor;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $app['encryptor'] = function ($app) {
            return new Encryptor(
                $app['config']['client_id'],
                $app['config']['token'],
                $app['config']['aes_key']
            );
        };


        $app['server'] = function ($app) {
            return new Guard($app);
        };
    }

}