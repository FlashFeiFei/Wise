<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/19
 * Time: 14:48
 */

namespace Wise\OpenPlatform\Auth;

use Wise\Kernel\AccessToken as BaseAccessToken;

class AccessToken extends BaseAccessToken
{
    /**
     * @var string
     */
    protected $requestMethod = 'GET';

    /**
     * @var string
     */
    protected $tokenKey = 'access_token';

    /**
     * @var string
     */
    protected $endpointToGetToken = 'public/2.0/smartapp/auth/tp/token';


    protected function getCredentials(): array
    {
        return [
            'client_id' => $this->app['config']['client_id'],
            'ticket' => $this->app['verify_ticket']->getTicket(),
        ];
    }

}