<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/22
 * Time: 11:50
 */

namespace Wise\OpenPlatform\Server;

use Wise\Kernel\ServerGuard;
use EasyWeChat\OpenPlatform\Server\Handlers\VerifyTicketRefreshed;
use Symfony\Component\HttpFoundation\Response;

class Guard extends ServerGuard
{
    const EVENT_COMPONENT_VERIFY_TICKET = 'ticket';

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resolve(): Response
    {
        $this->registerHandlers();

        $message = $this->getMessage();

        if (isset($message['InfoType'])) {
            $this->dispatch($message['InfoType'], $message);
        }

        return new Response(static::SUCCESS_EMPTY_RESPONSE);
    }

    /**
     * 注册监听器
     * Register event handlers.
     */
    protected function registerHandlers()
    {
        $this->on(self::EVENT_COMPONENT_VERIFY_TICKET, VerifyTicketRefreshed::class);
    }


}