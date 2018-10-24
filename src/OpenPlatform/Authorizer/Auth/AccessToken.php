<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/23
 * Time: 13:55
 */

namespace Wise\OpenPlatform\Authorizer\Auth;

use Wise\Kernel\AccessToken as BaseAccessToken;
use Wise\OpenPlatform\Application;
use Pimple\Container;

/**
 * Class AccessToken
 * 类继承了BaseAccessToken,当用refresh_token去换取token的时候
 * 发送请求的是http_client这个组件,该组件默认是没有任何请求中间件的
 * 智能小程序刷新token返回的数据格式和微信的一样，所以基类requestToken方法不需要重写
 * @package Wise\OpenPlatform\Authorizer\Auth
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
    protected $queryName = 'access_token';

    /**
     * 发送请求之后，token的字段名是什么
     * {@inheritdoc}.
     */
    protected $tokenKey = 'access_token';

    /**
     * @var Application
     */
    protected $component;

    /**
     * AuthorizerAccessToken constructor.
     *
     * @param \Pimple\Container $app
     * @param Application $component
     */
    public function __construct(Container $app, Application $component)
    {
        parent::__construct($app);
        //OpenPlatform的IOC容器
        $this->component = $component;
    }

    /**
     * 刷新token需要的数据
     * @return array
     */
    protected function getCredentials(): array
    {
        return [
            'grant_type' => 'app_to_tp_refresh_token',
            'refresh_token' => $this->app['config']['refresh_token'],
            //我们自己三方平台的tonken
            'access_token' => $this->component['access_token']->getToken()['access_token']
        ];
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return 'rest/2.0/oauth/token';
    }

}