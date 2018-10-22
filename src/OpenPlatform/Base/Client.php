<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/22
 * Time: 9:10
 */

namespace Wise\OpenPlatform\Base;

use Wise\Kernel\BaseClient;

class Client extends BaseClient
{
    /**
     * Create pre-authorization code.
     * 通过tick得到的token去获取预授权码
     *
     * @return \Psr\Http\Message\ResponseInterface|\Wise\Kernel\Support\Collection|array|object|string
     */
    public function createPreAuthorizationCode()
    {
        return $this->httpGet('rest/2.0/smartapp/tp/createpreauthcode');
    }

    /**
     * Get authorization info.
     * 使用授权码去获取授权用户的tonken
     * @param string|null $authorization_code
     *
     * @return mixed
     */
    public function handleAuthorize(string $authorization_code = null)
    {
        $params = [
            'code' => $authorization_code ?? $this->app['request']->get('authorization_code'),
            'grant_type' => 'app_to_tp_authorization_code'
        ];

        return $this->httpGet('rest/2.0/oauth/token', $params);
    }
}