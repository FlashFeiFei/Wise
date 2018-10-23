<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/23
 * Time: 16:37
 */

namespace Wise\OpenPlatform\Authorizer\Server;

use Wise\Kernel\ServerGuard;

class Guard extends ServerGuard
{
    /**
     * Get token from OpenPlatform encryptor.
     *
     * @return string
     */
    protected function getToken()
    {
        return $this->app['encryptor']->getToken();
    }
}