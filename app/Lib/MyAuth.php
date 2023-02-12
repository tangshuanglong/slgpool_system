<?php

namespace App\Lib;

use Swoft\Bean\Annotation\Mapping\Bean;


/**
 * 密码验证类
 * Class MyAuth
 * @package App\Lib
 * @Bean("MyAuth")
 */
class MyAuth
{
    /**
     * @var string
     */
    protected $hash_type = 'sha256';

    /**
     * @var string
     */
    protected $salt_len = 16;

    /**
     * @param int $size
     * @return string
     */
    private function generate_salt(int $size)
    {
        $base64 = base64_encode(md5(time().mt_rand(0,1000000), true));
        return substr($base64, 0, $size);
    }

    /**
     *
     * @param $password
     * @return array
     */
    public function generate_password($password): array
    {
        $salt = $this->generate_salt($this->salt_len);
        $password_hash = $this->generate_hash($password, $salt);
        return ['salt' => $salt, 'password_hash' => $password_hash];
    }

    /**
     * @param string $password
     * @param string $salt
     * @return string
     */
    private function generate_hash(string $password, string $salt): string
    {
        return hash_hmac($this->hash_type, $password, $salt);
    }

    /**
     * 密码验证
     * @param string $password
     * @param string $salt
     * @param string $hash_string
     * @return bool
     */
    public function password_auth(string $password, string $salt, string $hash_string): bool
    {
        $password_hash = $this->generate_hash($password, $salt);
        if (strcasecmp($password_hash, $hash_string) != 0) {
            return false;
        }
        return true;
    }
}
