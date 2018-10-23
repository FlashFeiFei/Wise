<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/19
 * Time: 14:48
 */

namespace Wise\OpenPlatform\Auth;

use Wise\Kernel\AccessToken as BaseAccessToken;
use Wise\Kernel\Exceptions\HttpException;

/**
 * Class AccessToken
 * 因为智能小程序的数据返回格式跟微信的不一样
 * 所以，需要修改一下基类的requestToken方法
 * @package Wise\OpenPlatform\Auth
 */
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

    /**
     * 修改积累的代码
     * @param array $credentials
     * @param bool $toArray
     * @return array|mixed|object|\Psr\Http\Message\ResponseInterface|string|\Wise\Kernel\Support\Collection
     * @throws HttpException
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function requestToken(array $credentials, $toArray = false)
    {
        $response = $this->sendRequest($credentials);
        $result = json_decode($response->getBody()->getContents(), true);
        $formatted = $this->castResponseToType($response, $this->app['config']->get('response_type'));
        //上面的代码都是照抄基类的


        //开始修改代码
        if (empty($result['data'])) {
            throw new HttpException('Request access_token fail: ' . json_encode($result, JSON_UNESCAPED_UNICODE), $response, $formatted);
        }
        $result = $result['data'];
        //结束修改代码

        //下面的代码还是照抄基类的
        return $toArray ? $result : $formatted;
    }


}