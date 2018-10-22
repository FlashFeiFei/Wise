<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/22
 * Time: 14:25
 */

namespace Wise\Kernel\Messages;


class Raw extends Message
{
    /**
     * @var string
     */
    protected $type = 'raw';

    /**
     * Properties.
     *
     * @var array
     */
    protected $properties = ['content'];


    /**
     * Constructor.
     *
     * @param string $content
     */
    public function __construct(string $content)
    {
        parent::__construct(['content' => strval($content)]);
    }


    /**
     * @param array $appends
     * @param bool $withType
     *
     * @return array
     */
    public function transformForJsonRequest(array $appends = [], $withType = true): array
    {
        return json_decode($this->content, true) ?? [];
    }

    public function __toString()
    {
        return $this->get('content') ?? '';
    }
}