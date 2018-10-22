<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/22
 * Time: 16:45
 */

namespace Wise\OpenPlatform\Server\Handlers;

use Wise\Kernel\Contracts\EventHandlerInterface;
use Wise\OpenPlatform\Application;

class VerifyTicketRefreshed implements EventHandlerInterface
{
    /**
     * @var \Wise\OpenPlatform\Application
     */
    protected $app;

    /**
     * Constructor.
     *
     * @param \Wise\OpenPlatform\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle($payload = null)
    {
        if (!empty($payload['Ticket'])) {
            $this->app['verify_ticket']->setTicket($payload['Ticket']);
        }
    }
}