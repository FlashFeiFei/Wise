<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/23
 * Time: 17:10
 */

namespace Wise\OpenPlatform\Authorizer\MiniProgram\Auth;

use Wise\Kernel\BaseClient;
use Wise\Kernel\ServiceContainer;
use Wise\OpenPlatform\Application;

class Client extends BaseClient
{
    /**
     * @var \Wise\OpenPlatform\Application
     */
    protected $component;

    /**
     * Client constructor.
     *
     * @param \Wise\Kernel\ServiceContainer $app
     * @param \Wise\OpenPlatform\Application $component
     */
    public function __construct(ServiceContainer $app, Application $component)
    {
        parent::__construct($app);

        $this->component = $component;
    }

    /**
     * TP代授权小程序登录
     * Get session info by code.
     *
     * 前端传给我的code
     * @param string $code
     *
     * @return \Psr\Http\Message\ResponseInterface|\Wise\Kernel\Support\Collection|array|object|string
     *
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function session(string $code)
    {
        $params = [
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];

        return $this->httpGet('rest/2.0/oauth/getsessionkeybycode', $params);
    }

    /**
     * 自己写的方法
     * 获取小程序基础信息
     */
    public function appInfo()
    {
        return $this->httpGet('rest/2.0/smartapp/app/info');
    }
}