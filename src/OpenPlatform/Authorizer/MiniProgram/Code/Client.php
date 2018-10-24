<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/24
 * Time: 10:22
 */

namespace Wise\OpenPlatform\Authorizer\MiniProgram\Code;

use Wise\Kernel\BaseClient;

class Client extends BaseClient
{
    /**
     * 为授权小程序上传代码
     * @param int $templateId
     * @param string $extJson
     * @param string $version
     * @param string $description
     *
     * @return array|\Wise\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function commit(int $templateId, string $extJson, string $version, string $description)
    {
        return $this->httpPostJson('rest/2.0/smartapp/package/upload', [
            'template_id' => $templateId,
            'ext_json' => $extJson,
            'user_version' => $version,
            'user_desc' => $description,
        ]);
    }

    /**
     * 获取授权小程序预览包详情
     * 我猜这应该是最新的一次的信息
     * 接口会返回用户小程序的package_id
     * 当前用户小程序的的状态
     * @return array|object|\Psr\Http\Message\ResponseInterface|string|\Wise\Kernel\Support\Collection
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function packageGettrial()
    {
        return $this->httpGet('rest/2.0/smartapp/package/gettrial');
    }

    /**
     * 获取小程序包列表
     * @return array|object|\Psr\Http\Message\ResponseInterface|string|\Wise\Kernel\Support\Collection
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function packageGet()
    {
        return $this->httpGet('rest/2.0/smartapp/package/get');
    }

    /**
     * 获取授权小程序包详情
     * 小程序状态，不指定package_id的情况下默认是线上版本
     * @param int|null $type
     * 代码包id
     * @param string|null $package_id
     * @return array|object|\Psr\Http\Message\ResponseInterface|string|\Wise\Kernel\Support\Collection
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function packageGetdetail(int $type = null, string $package_id = null)
    {
        if (!empty($type) && !empty($package_id)) {
            $query = [
                'type' => $type,
                'package_id' => $package_id
            ];
        }
        return $this->httpGet('rest/2.0/smartapp/package/getdetail', $query ?? []);
    }

    /**
     * 为授权的小程序提交审核
     *
     * 送审描述
     * @param string $content
     * 代码包id
     * @param string $package_id
     * 备注
     * @param string $remark
     *
     * @return array|\Wise\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function submitAudit(string $content, string $package_id, string $remark)
    {
        return $this->httpPostJson('rest/2.0/smartapp/package/submitaudit', [
            'content' => $content,
            'package_id' => $package_id,
            'remark' => $remark
        ]);
    }

    /**
     * 小程序审核撤回
     * @param string $package_id
     * @return array|\Wise\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function withdrawAudit(string $package_id)
    {
        return $this->httpPostJson('rest/2.0/smartapp/package/withdraw', [
            'package_id' => $package_id
        ]);
    }

    /**
     * 发布已通过审核的小程序
     * @param string $package_id
     * @return array|\Wise\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function release(string $package_id)
    {
        return $this->httpPostJson('rest/2.0/smartapp/package/release', [
            'package_id' => $package_id
        ]);
    }

    /**
     * 小程序版本回退
     * @param string $package_id
     * @return array|\Wise\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function rollbackRelease($package_id)
    {
        return $this->httpPostJson('rest/2.0/smartapp/package/rollback', [
            'package_id' => $package_id
        ]);
    }


}