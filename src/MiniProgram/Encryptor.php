<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/23
 * Time: 16:46
 */

namespace Wise\MiniProgram;

use Wise\Kernel\Encryptor as BaseEncryptor;
use Wise\Kernel\Exceptions\DecryptException;
use Wise\Kernel\Support\AES;

class Encryptor extends BaseEncryptor
{
    /**
     * 这个方法没有改还是照抄的
     * Decrypt data.
     *
     * @param string $sessionKey
     * @param string $iv
     * @param string $encrypted
     *
     * @return array
     */
    public function decryptData(string $sessionKey, string $iv, string $encrypted): array
    {
        $decrypted = AES::decrypt(
            base64_decode($encrypted, false), base64_decode($sessionKey, false), base64_decode($iv, false)
        );

        $decrypted = json_decode($this->pkcs7Unpad($decrypted), true);

        if (!$decrypted) {
            throw new DecryptException('The given payload is invalid.');
        }

        return $decrypted;
    }
}