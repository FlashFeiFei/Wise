<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/19
 * Time: 14:36
 */

namespace Wise\OpenPlatform;

use Wise\Kernel\ServiceContainer;
use Wise\OpenPlatform\Authorizer\Auth\AccessToken;
use Wise\OpenPlatform\Authorizer\MiniProgram\Application as MiniProgram;
use Wise\OpenPlatform\Authorizer\Server\Guard;
use Wise\MiniProgram\Encryptor;
use Wise\OpenPlatform\Authorizer\MiniProgram\Auth\Client;

/**
 * Class Application
 * @package Wise\OpenPlatform
 * @property \Wise\OpenPlatform\Server\Guard $server
 * @method mixed createPreAuthorizationCode()
 * @method mixed handleAuthorize(string $authCode = null)
 */
class Application extends ServiceContainer
{
    /**
     * @var array
     */
    protected $providers = [
        Auth\ServiceProvider::class,
        //base组件
        Base\ServiceProvider::class,
        //用户接受百度定时推送过来的ticke
        Server\ServiceProvider::class,
        //小程序草稿、模板管理
        CodeTemplate\ServiceProvider::class,
    ];

    /**
     * @var array
     */
    protected $defaultConfig = [
        'http' => [
            'timeout' => 5.0,
            'base_uri' => 'https://openapi.baidu.com/',
        ],
    ];

    /**
     * Return the pre-authorization login page url.
     *
     * @param string $callbackUrl
     * @param string|array|null $optional
     *
     * @return string
     */
    public function getPreAuthorizationUrl(string $callbackUrl, $optional = []): string
    {
        // 兼容旧版 API 设计
        if (\is_string($optional)) {
            $optional = [
                'pre_auth_code' => $optional,
            ];
        } else {
            $optional['pre_auth_code'] = $this->createPreAuthorizationCode()['pre_auth_code'];
        }

        $queries = \array_merge($optional, [
            'client_id' => $this['config']['client_id'],
            'redirect_uri' => $callbackUrl,
        ]);

        return 'http://smartprogram.baidu.com/mappconsole/tp/authorization?' . http_build_query($queries);
    }

    /**
     * 合并一些参数进运行时config组件，需要的时候再从config组建中获取
     * @param string $appId
     * @param string|null $refreshToken
     *
     * @return array
     */
    protected function getAuthorizerConfig(string $appId, string $refreshToken = null): array
    {
        return $this['config']->merge([
            'app_id' => $appId,
            'refresh_token' => $refreshToken,
        ])->toArray();
    }

    /**
     * 创建授权用户的智能小程序
     * Creates the miniProgram application.
     * 授权用户智能小程序的appi
     * @param string $appId
     * 授权用户的refresh_token
     * @param string|null $refreshToken
     * @param \Wise\OpenPlatform\Authorizer\Auth\AccessToken|null $accessToken
     *
     * @return \Wise\OpenPlatform\Authorizer\MiniProgram\Application
     */
    public function miniProgram(string $app_id, string $refresh_token = null, AccessToken $access_token = null): MiniProgram
    {
        //第一个参数配置文件
        return new MiniProgram($this->getAuthorizerConfig($app_id, $refresh_token), $this->getReplaceServices($access_token) + [
                'encryptor' => function () {
                    //小程序的加解码组件,目前这个电脑关系没有做任何事情,智能小程序好像没有事件推送，所以这一步暂时还是安全的
                    return new Encryptor($this['config']['app_id'], $this['config']['token'], $this['config']['aes_key']);
                },

                'auth' => function ($app) {
                    return new Client($app, $this);
                },
            ]);
    }

    /**
     * @param \Wise\OpenPlatform\Authorizer\Auth\AccessToken|null $accessToken
     *
     * @return array
     */
    protected function getReplaceServices(AccessToken $accessToken = null): array
    {
        $services = [
            'access_token' => $accessToken ?: function ($app) {
                //授权用户的token组件
                return new AccessToken($app, $this);
            },

            //小程序的事件监听器框架server
            //目前智能小程序并没有向微信小程序那边有事件推送服务器，所以这个服务替换暂时还是安全的，不会影响代码
            'server' => function ($app) {
                return new Guard($app);
            },
        ];


        foreach (['cache', 'http_client', 'log', 'logger', 'request'] as $reuse) {
            if (isset($this[$reuse])) {
                $services[$reuse] = $this[$reuse];
            }
        }

        return $services;
    }

    /**
     * Handle dynamic calls.
     *
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->base->$method(...$args);
    }
}