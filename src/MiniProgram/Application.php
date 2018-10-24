<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/23
 * Time: 16:06
 */

namespace Wise\MiniProgram;

use Wise\Kernel\ServiceContainer;

class Application extends ServiceContainer
{
    protected $providers = [
        //小程序二维码服务提供者
        AppCode\ServiceProvider::class,
    ];
}