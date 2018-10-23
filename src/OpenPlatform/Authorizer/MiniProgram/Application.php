<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/23
 * Time: 16:01
 */

namespace Wise\OpenPlatform\Authorizer\MiniProgram;

use Wise\MiniProgram\Application as MiniProgram;
use Wise\OpenPlatform\Authorizer\Aggregate\AggregateServiceProvider;

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
        parent::__construct($config, $prepends);

        $providers = [
            AggregateServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->register(new $provider());
        }
    }
}