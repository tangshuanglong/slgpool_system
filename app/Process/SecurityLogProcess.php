<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Process;

use App\Lib\MyRabbitMq;
use App\Model\Entity\UserSecurityLog;
use App\Model\Entity\UserSecurityType;
use PhpAmqpLib\Message\AMQPMessage;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Log\Helper\CLog;
use Swoft\Process\Annotation\Mapping\Process;
use Swoft\Process\Contract\ProcessInterface;
use Swoft\Stdlib\Helper\JsonHelper;
use Swoole\Coroutine;
use Swoole\Process\Pool;
use App\Lib\MyCommon;

/**
 * Class Worker1Process
 *
 * @since 2.0
 *
 * @Process(workerId={0})
 */
class SecurityLogProcess implements ProcessInterface
{

    /**
     * @Inject()
     * @var MyRabbitMq
     */
    private $myRabbitMq;

    /**
     * @Inject()
     * @var MyCommon
     */
    private $myCommon;

    private $log_path = '/logs/security_log';

    /**
     * @param Pool $pool
     * @param int  $workerId
     */
    public function run(Pool $pool, int $workerId): void
    {
        $channel = $this->myRabbitMq->pop('security_log_key', [$this, 'callback']);
        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
    }

    /**
     * 回调函数
     * @param $msg
     */
    public function callback(AMQPMessage $msg)
    {
        $data = JsonHelper::decode($msg->body, true);
        MyCommon::write_log(print_r($data, true), $this->log_path);
        $type_info = UserSecurityType::select('id')->where(['type_name_en' => $data['type_name']])->first();
        if (empty($type_info)) {
            MyCommon::write_log('security type error', $this->log_path);
            $this->myRabbitMq->back_ack($msg);
            return ;
        }
        $data['type_id'] = $type_info['id'];
        $data['create_time'] = time();
        $res = UserSecurityLog::insert($data);
        if (!$res){
            MyCommon::write_log('数据操作失败', $this->log_path);
            $this->myRabbitMq->back_nack($msg);
        }else{
            MyCommon::write_log('数据操作成功', $this->log_path);
            $this->myRabbitMq->back_ack($msg);
        }
    }
}
