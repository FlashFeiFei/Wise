<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/19
 * Time: 17:25
 */

namespace Wise\OpenPlatform\Auth;

use Wise\Kernel\Exceptions\RuntimeException;
use Wise\Kernel\Traits\InteractsWithCache;
use Wise\OpenPlatform\Application;

class VerifyTicket
{
    use InteractsWithCache;

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

    /**
     * Put the credential `component_verify_ticket` in cache.
     *
     * @param string $ticket
     *
     * @return $this
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setTicket(string $ticket)
    {
        $this->getCache()->set($this->getCacheKey(), $ticket, 3600);

        return $this;
    }

    /**
     * Get the credential `component_verify_ticket` from cache.
     *
     * @return string
     *
     * @throws \Wise\Kernel\Exceptions\RuntimeException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getTicket(): string
    {
        if ($cached = $this->getCache()->get($this->getCacheKey())) {
            return $cached;
        }

        throw new RuntimeException('Credential "component_verify_ticket" does not exist in cache.');
    }

    /**
     * Get cache key.
     *
     * @return string
     */
    protected function getCacheKey(): string
    {
        return 'wise.open_platform.verify_ticket.' . $this->app['config']['client_id'];
    }


}