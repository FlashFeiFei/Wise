<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/22
 * Time: 11:50
 */

namespace Wise\OpenPlatform\Server;

use Wise\Kernel\ServerGuard;
use Wise\OpenPlatform\Server\Handlers\VerifyTicketRefreshed;
use Symfony\Component\HttpFoundation\Response;

class Guard extends ServerGuard
{
    const MESAGE_TYPE_COMPONENT_VERIFY_TICKET = 'ticket';

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resolve(): Response
    {
        //注册一下默认要处理的事件监听器
        $this->registerHandlers();
        //从协议中获取数据，这里指的是从百度的推送事件中获取数据,是解码之后的数据
        $message = $this->getMessage();

        if (isset($message['MsgType'])) {
                $this->dispatch($this->msgTypeChangeInt($message['MsgType']), $message);
        }

        return new Response(static::SUCCESS_EMPTY_RESPONSE);
    }

    /**
     * 注册监听器
     * Register event handlers.
     */
    protected function registerHandlers()
    {
        //注册消息类型为ticket事件为push的监听器
        $this->on($this->msgTypeChangeInt(self::MESAGE_TYPE_COMPONENT_VERIFY_TICKET), VerifyTicketRefreshed::class);
    }


}