<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/19
 * Time: 14:36
 */

namespace Wise\OpenPlatform;

use Wise\Kernel\ServiceContainer;

/**
 * Class Application
 * @package Wise\OpenPlatform
 * @method mixed createPreAuthorizationCode()
 */
class Application extends ServiceContainer
{
    /**
     * @var array
     */
    protected $providers = [
        Auth\ServiceProvider::class,
        Base\ServiceProvider::class,
        Server\ServiceProvider::class,
        CodeTemplate\ServiceProvider::class,
    ];

    /**
     * @var array
     */
    protected $defaultConfig = [
        'http' => [
            'timeout' => 5.0,
            'base_uri' => 'https://api.weixin.qq.com/',
        ],
    ];

    /**
     * Return the pre-authorization login page url.
     *
     * @param string            $callbackUrl
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
            'component_appid' => $this['config']['app_id'],
            'redirect_uri' => $callbackUrl,
        ]);

        return 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?'.http_build_query($queries);
    }

    /**
     * Handle dynamic calls.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->base->$method(...$args);
    }
}