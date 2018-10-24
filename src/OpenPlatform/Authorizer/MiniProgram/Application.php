<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/23
 * Time: 16:01
 */

namespace Wise\OpenPlatform\Authorizer\MiniProgram;

use Wise\MiniProgram\Application as MiniProgram;

class Application extends MiniProgram
{
    /**
     * Application constructor.
     * 配置文件参数，会合并到配置运行时的配置文件中
     * @param array $config
     * @param array $prepends
     */
    public function __construct(array $config = [], array $prepends = [])
    {
        //通过构造函数，注册一些东西
        parent::__construct($config, $prepends);

        $providers = [
            //智能小程序授权代替用户上传代码等
            Code\ServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->register(new $provider());
        }
    }
}