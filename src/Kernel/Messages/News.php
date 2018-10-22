<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/22
 * Time: 14:09
 */

namespace Wise\Kernel\Messages;


class News extends Message
{
    /**
     * @var string
     */
    protected $type = 'news';

    /**
     * @var array
     */
    protected $properties = [
        'items',
    ];

    /**
     * News constructor.
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct(compact('items'));
    }

    /**
     * @param array $data
     * @param array $aliases
     *
     * @return array
     */
    public function propertiesToArray(array $data, array $aliases = []): array
    {
        return ['articles' => array_map(function ($item) {
            if ($item instanceof NewsItem) {
                return $item->toJsonArray();
            }
        }, $this->get('items'))];
    }

    public function toXmlArray()
    {
        $items = [];

        foreach ($this->get('items') as $item) {
            if ($item instanceof NewsItem) {
                $items[] = $item->toXmlArray();
            }
        }

        return [
            'ArticleCount' => count($items),
            'Articles' => $items,
        ];
    }
}