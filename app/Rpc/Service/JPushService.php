<?php

namespace App\Rpc\Service;

use App\Rpc\Lib\JPushInterface;
use JPush\Client;
use JPush\Exceptions\JPushException;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

/**
 * Class JPushService
 * @package  App\Rpc\Service
 * @Service()
 */
class JPushService implements JPushInterface
{

    protected $appKey;

    protected $masterSecret;

    protected $logPath;

    public function __construct()
    {
        $this->appKey = config('app.jpush.app_key');
        $this->masterSecret = config('app.jpush.master_secret');
        $this->logPath = config('app.jpush.log_path');
    }

    /**
     * 推送消息给所有设备
     *
     * @param $title
     * @param $content
     * @return array
     */
    public function pushToAll($title, $content)
    {
        $client = new Client($this->appKey, $this->masterSecret, $this->logPath);

        $iosNotification = array(
            'sound' => $title,
            'badge' => 2,
            'content-available' => true,
        );
        $androidNotification = array(
            'title' => $title,
            'builder_id' => 2,
        );
        $message = array(
            'title' => $title,
            'content_type' => 'text',
        );

        return $client->push()
            ->setPlatform('all') //推送设备，这里是所有 [ios, android, winphone]
            ->setAudience('all')
            ->iosNotification($content, $iosNotification)
            ->androidNotification($content, $androidNotification)
            ->message($content, $message)
            ->send();
    }

    /**
     * 推送消息给指定设备
     *
     * @param $regId
     * @param $title
     * @param $content
     * @return array
     */
    public function pushToRegId($regId, $title, $content)
    {
        $client = new Client($this->appKey, $this->masterSecret, $this->logPath);

        $iosNotification = array(
            'sound' => $title,
            'badge' => 2,
            'content-available' => true,
        );
        $androidNotification = array(
            'title' => $title,
            'builder_id' => 2,
        );
        $message = array(
            'title' => $title,
            'content_type' => 'text',
        );

        return $client->push()
            ->setPlatform('all') //推送设备，这里是所有 [ios, android, winphone]
            ->setAudience('all')
            ->addRegistrationId($regId) // 指定推送的regId，regId是前端在调用极光接口注册的regId，存储在后端服务器与用户关联
            ->iosNotification($content, $iosNotification)
            ->androidNotification($content, $androidNotification)
            ->message($content, $message)
            ->send();
    }

}
