<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/19
 * Time: 14:01
 */

namespace Wise\Kernel\Contracts;

use ArrayAccess;

interface Arrayable extends ArrayAccess
{
    /**
     * Get the instance as an array.
     * @return array
     */
    public function toArray();
}