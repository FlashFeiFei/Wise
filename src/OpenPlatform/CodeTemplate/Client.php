<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/23
 * Time: 10:55
 */

namespace Wise\OpenPlatform\CodeTemplate;

use Wise\Kernel\BaseClient;

class Client extends BaseClient
{
    /**
     * 草稿列表
     * @param int $page
     * @param int $page_size
     * @return array|object|\Psr\Http\Message\ResponseInterface|string|\Wise\Kernel\Support\Collection
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function getDrafts($page = 1, $page_size = 10)
    {
        $query = [
            'page' => $page,
            'page_size' => $page_size
        ];
        return $this->httpGet('rest/2.0/smartapp/template/gettemplatedraftlist', $query);
    }

    /**
     * 将草稿箱的草稿选为小程序代码模版.
     * @param int $draftId
     * @return array|object|\Psr\Http\Message\ResponseInterface|string|\Wise\Kernel\Support\Collection
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function createFromDraft(int $draftId)
    {
        $params = [
            'draft_id' => $draftId,
        ];

        return $this->httpPostJson('rest/2.0/smartapp/template/addtotemplate', $params);
    }

    /**
     * 获取代码模版库中的所有小程序代码模版.
     * @return array|object|\Psr\Http\Message\ResponseInterface|string|\Wise\Kernel\Support\Collection
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function list($page = 1, $page_size = 10)
    {
        $query = [
            'page' => $page,
            'page_size' => $page_size
        ];

        return $this->httpGet('rest/2.0/smartapp/template/gettemplatelist', $query);
    }

    /**
     * 删除指定小程序代码模版.
     * @param $templateId
     * @return array|object|\Psr\Http\Message\ResponseInterface|string|\Wise\Kernel\Support\Collection
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function delete($templateId)
    {
        $params = [
            'template_id' => $templateId,
        ];

        return $this->httpPostJson('rest/2.0/smartapp/template/deltemplate', $params);
    }

}