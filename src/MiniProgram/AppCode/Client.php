<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/24
 * Time: 11:27
 */

namespace Wise\MiniProgram\AppCode;

use Wise\Kernel\BaseClient;
use Wise\Kernel\Http\StreamResponse;

class Client extends BaseClient
{

    /**
     * Get stream.
     *
     * @param string $endpoint
     * @param array $params
     *
     * @return \Wise\Kernel\Http\StreamResponse
     */
    protected function getStream(string $endpoint, array $params)
    {
        return StreamResponse::buildFromPsrResponse($this->requestRaw($endpoint, 'POST', ['json' => $params]));
    }
}