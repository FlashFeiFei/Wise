<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/24
 * Time: 11:28
 */

namespace Wise\Kernel\Http;

use Wise\Kernel\Exceptions\InvalidArgumentException;
use Wise\Kernel\Exceptions\RuntimeException;
use Wise\Kernel\Support\File;

class StreamResponse extends Response
{
    /**
     * 将图片下载到服务器指定路径
     * @param string $directory
     * 给图片起一个名字
     * @param string $filename
     *
     * @return bool|int
     *
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     * @throws \Wise\Kernel\Exceptions\RuntimeException
     */
    public function save(string $directory, string $filename = '')
    {
        $this->getBody()->rewind();
        //文件存放路径
        $directory = rtrim($directory, '/');

        if (!is_dir($directory)) {
            //创建目录
            mkdir($directory, 0755, true); // @codeCoverageIgnore
        }
        //判断文件路径是否可写
        if (!is_writable($directory)) {
            throw new InvalidArgumentException(sprintf("'%s' is not writable.", $directory));
        }

        $contents = $this->getBody()->getContents();

        if (empty($contents) || '{' === $contents[0]) {
            throw new RuntimeException('Invalid media response content.');
        }

        if (empty($filename)) {
            if (preg_match('/filename="(?<filename>.*?)"/', $this->getHeaderLine('Content-Disposition'), $match)) {
                $filename = $match['filename'];
            } else {
                $filename = md5($contents);
            }
        }

        if (empty(pathinfo($filename, PATHINFO_EXTENSION))) {
            $filename .= File::getStreamExt($contents);
        }

        file_put_contents($directory . '/' . $filename, $contents);

        return $filename;
    }

    /**
     * @param string $directory
     * @param string $filename
     *
     * @return bool|int
     *
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     */
    public function saveAs(string $directory, string $filename)
    {
        return $this->save($directory, $filename);
    }
}