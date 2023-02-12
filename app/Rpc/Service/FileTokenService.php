<?php

namespace App\Rpc\Service;

use App\Lib\MyFileToken;
use App\Model\Entity\Captcha;
use App\Rpc\Lib\FileTokenInterface;
use App\Rpc\Lib\VerifyCodeInterface;
use App\Rpc\Lib\VerifyInterface;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Bean\BeanFactory;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

/**
 * Class VerifyCodeService
 * @package App\Rpc\Service
 * @Service()
 */
class FileTokenService implements FileTokenInterface{


    /**
     * @Inject()
     * @var MyFileToken
     */
    private $myFileToken;

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function generate_token()
    {
        return $this->myFileToken->generateToken(config('file_url'));
    }
}
