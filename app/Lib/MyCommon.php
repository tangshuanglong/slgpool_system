<?php

namespace App\Lib;
use GuzzleHttp\Client;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\BeanFactory;
use Swoft\Http\Message\Request;
use Swoft\Redis\Redis;
use App\Lib\MyValidator;
use App\Lib\MyRedisHelper;

/**
 * 公共函数类
 * Class MyCommon
 * @package App\Lib
 * @Bean("MyCommon")
 */
class MyCommon
{

	/**
     * 获取毫秒数时间戳
     * @return false|float
     */
    public function getMillisecond()
    {
        $microtime = microtime(true);
        return (round($microtime * 1000));
    }

    /**
     * 判断是否是邮箱
     * @param string $email
     * @return bool
     */
    public function is_email(string $email): bool
    {
        preg_match('/^[A-Za-z0-9][\w\-\.]+\@([\w\-])+\.[\w\-]+$/',$email,$rs);
        if(empty($rs)){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 判断是否是手机号码
     * @param string $mobile
     * @param string $area_code
     * @return bool
     */
    public function is_mobile(string $mobile, string $area_code): bool
    {
        $preg = config('mobile_preg.'.$area_code);
        if (!$preg){
            //默认86
            $preg = config('mobile_preg.86');
        }
        preg_match($preg, $mobile,$rs);
        if(empty($rs)){
            return false;
        }else{
            return true;
        }
    }

    /**
    *写入日志
    */
    public static function write_log($data, $path = '/usr/local/logs/default')
    {
        if (!is_string($data)) {
            return false;
        }
        $filename = $path .'/'. date("Y_m_d") . '.log';
        if(!is_dir($path)){
            mkdir($path, 0777, true);
        }
        $time = date("Y-m-d H:i:s");
        $content = "日期：".$time."----信息：".$data . PHP_EOL;
        //异步写入日志
        file_put_contents($filename, $content, FILE_APPEND);
        unset($data, $path, $filename, $time, $content);
        return true;
    }

    /**
     * @param $account
     * @param $area_code
     * @param $temp_key
     * @param $action_name
     * @param $send_data
     * @return bool|int
     */
    public function push_notice_queue(string $account, string $area_code, string $temp_key, string $action_name = '', array $send_data = []): bool
    {
        /**@var MyValidator $myValidator */
        $myValidator = BeanFactory::getBean('MyValidator');
        $account_type = $myValidator->account_check($account, $area_code);
        /**@var MyRabbitMq $myRabbitMq */
        $myRabbitMq = BeanFactory::getBean('MyRabbitMq');
        $data = [
            'unique_id' => $this->get_unique_id($account),
            'type' => $account_type,
            'account' => $account,
            'area_code' => $area_code,
            'temp_key' => $temp_key,
            'action_name' => $action_name,
            'send_data' => $send_data
        ];
        return $myRabbitMq->push('notice_list_key', $data);

    }

    /**
     * 创建唯一id
     * @param string $prefix
     * @return string
     */
    public function get_unique_id($prefix = ''): string
    {
        $rand = mt_rand(0, 10000);
        $time = $this->getMillisecond();
        return $prefix.'_'.$time.'_'.$rand;
    }


    /**
     * 拼接正确的七牛云访问路径
     * @param string $path
     * @param string $type
     * @return string
     */
	public static function get_filepath(string $path, string $type = 'qiniu')
	{
		if(empty($path)){
			return '';
		}
		if ($type === 'qiniu') {
            return config('qi_niu_domain') . '/' . $path;
        } else {
            return config('file_url') . '/' . $path;
        }

	}

    /**
     * 解析出文件名 是一个base64编码的字符串，存入数据库使用
     * @param string $path
     * @param string $type
     * @return bool|false|string
     */
    public static function get_filename(string $path, string $type = 'qiniu')
    {
        if ($type === 'qiniu') {
            $domain = config('qi_niu_domain');
        } else {
            $domain = config('file_url');
        }
        $pos_res = strpos($path, $domain);
        if($pos_res === false || $pos_res != 0){
            return false;
        }
        $len = strlen($domain . '/');
        return substr($path, $len);
    }

    /**
     * 检查字符串是否为空，为空返回true, 否则返回false
     * @param $str
     * @return bool
     */
    public static function checkEmpty($str)
    {
        if(!isset($str)){
            return true;
        }
        if($str === NULL){
            return true;
        }
        if(trim($str) === ''){
            return true;
        }
        return false;
    }

    //获取当前时间到结束时间的秒数
    public function get_cur_day_time($endtime)
    {
        $diff_time = date("Y-m-d H:i:s", $endtime);
        return strtotime($diff_time) - time();
    }

    //手机号码中间加六个星号
    public function phoneCipher($phone, $start, $len)
    {
        $chiper = '*';
        for($i = 1; $i < $len; $i++){
            $chiper .= '*';
        }
        return substr_replace($phone, $chiper, $start, $len);
    }

    //姓名加星号
    public function nameCipher($name, $start, $len)
    {
        $start = $start*3;
        $chiper = '*';
        for($i = 1; $i < $len; $i++){
            $chiper .= '*';
        }
        $len = $len*3;
        return substr_replace($name, $chiper, $start, $len);
    }

    //创建随机手机号
    public function createPhone()
    {
        $data = [134,135,136,137,138,139,147,150,151,152,157,158,159,178,182,183,184,187,188,130,131,132,155,156,185,186,145,176];
        $len = count($data);
        $prefix = $data[mt_rand(0, ($len - 1))];
        $middle = str_pad(mt_rand(0,9999), 4, 0, STR_PAD_LEFT);
        $suffix = mt_rand(1027, 9851);
        return $prefix . $middle . $suffix;
    }

    public function createEmail($phone)
    {
        $data = ['163', 'qq'];
        $len = count($data);
        $company = $data[mt_rand(0, ($len - 1))];
        if($company === 'qq'){
            $email = mt_rand(1000000, 2345678910) . '@qq.com';
        }else{
            $email = $phone . '@163.com';
        }
        return $email;
    }

    /**
     * 防止重复点击
     * @param $key
     * @param int $timeout
     * @return bool
     */
    public function can_not_repeat_click(string $key, int $timeout = 2): bool
    {
        $script = '
            if redis.call("incr", KEYS[1]) then
                if redis.call("expire", KEYS[1], ARGV[1]) then
                    return redis.call("get", KEYS[1])
                end
            else
                return 2
            end
        ';
        $is_ex = Redis::eval($script, [$key, $timeout], 1);
        if($is_ex > 1){
            return true;
        }
        return false;
    }

    //取消点击状态
    public function close_click_status($key)
    {
        return Redis::del($key);
    }


    //根据name获取配置表信息
    public function config_info($name, $group = 0)
    {
        if($group === 0){
            $data = $this->ci->db->where(['name' => $name, 'cancel_flag' => 0])->get('config')->row_array();
        }else{
            $data = $this->ci->db->where(['name' => $name, 'group' => $group, 'cancel_flag' => 0])->get('config')->row_array();
        }
        return $data;
    }

    //根据group获取配置表信息
    public function config_info_group($group)
    {
        $data = $this->ci->db->where(['group' => $group, 'cancel_flag' => 0])->get('config')->result_array();
        return $data;
    }

    //获取字符串的余数hash值
    public function get_hash_id($key, $remainder)
    {
        //如果是数字，取余返回
        if(is_numeric($key)){
            return bcmod($key, $remainder);
        }
        $str_num = crc32(strtolower($key));
        return bcmod($str_num, $remainder);
    }

    /**
     * 获取客户端ip
     * @param Request $request
     * @return mixed
     */
    public static function get_ip(Request $request): string
    {
        $services = array_merge($request->getHeaderLines(), $request->getServerParams());
        $ip = $services['remote_addr'];
        if (isset($services['x-forwarded-for'])){
            $ip = explode(',', $services['x-forwarded-for'])[0];
        }
        return $ip;
    }

    /**
     * 获取ip归属地
     * @param string $ip
     * @return string
     */
    public function get_ip_area(string $ip): string
    {
        $myIp = new MyIP();
        $ip_info = $myIp->find($ip);
        return implode(' ', $ip_info);
    }

    /**
     * 获取浮点数长度
     * @param type $num
     * @return type
     */
    public function getFloatLength($num) {
        $count = 0;
        $temp = explode ( '.', $num );
        if (sizeof ( $temp ) > 1) {
            $decimal = end ( $temp );
            $count = strlen ( $decimal );
        }
        return $count;
    }

    //去除掉所有空格
    public function trimall($str)
    {
        $find = [' ', "\n", "\r", "\t"];
        $replace = ['','','',''];
        return str_replace($find, $replace, $str);
    }

    //冒泡排序，降序
    public function bubble_rsort($data, $key)
    {
        $count = count($data);
        if($count > 1){
            for($i = 1; $i < $count; $i++){
                for($j = 0; $j < $count - $i; $j++){
                    if($data[$j][$key] < $data[$j+1][$key]){
                        $tmp = $data[$j+1];
                        $data[$j+1] = $data[$j];
                        $data[$j] = $tmp;
                        unset($tmp);
                    }
                }
            }
        }
        return $data;
    }

    //冒泡排序，升序
    public function bubble_sort($data, $key)
    {
        $count = count($data);
        if($count > 1){
            for($i = 1; $i < $count; $i++){
                for($j = 0; $j < $count - $i; $j++){
                    if($data[$j][$key] > $data[$j+1][$key]){
                        $tmp = $data[$j+1];
                        $data[$j+1] = $data[$j];
                        $data[$j] = $tmp;
                        unset($tmp);
                    }
                }
            }
        }
        return $data;
    }

    //获取平均值
    public function get_aver_value(array $datas)
    {
        $sum = 0;
        $len = count($datas);
        foreach ($datas as $value){
            $sum += $value;
        }
        return bcdiv($sum, $len, 6);
    }


    /**
     * 发送验证码
     * @param $account
     * @param $area_code
     * @param $action
     * @return bool|int
     */
    public function send_verify_code(string $account, string $area_code, string $action)
    {
        $code_key = $action. '_code_key';
        mt_srand();
        $code = mt_rand(100000, 999999);
        $action_name = config('app.actions.'.$action);
        $send_data = [
            $code,
            $action_name,
            config('app.code_expire_time'),
        ];
        $temp_key = 'code_temp_id';
        $res = $this->push_notice_queue($account, $area_code, $temp_key, $action_name, $send_data);
        if ($res){
            $set_redis = [
                'create_time' => time(),
                'code' => $code,
            ];
            $res = MyRedisHelper::hSet($code_key, $account, $set_redis);
            if ($res) {
                return $code;
            }
            return false;
        }
        return false;
    }

    /**
     * 是否是重复数据
     * @param $key
     * @return bool
     */
    public function is_repeat_data($key)
    {
        return Redis::exists($key);
    }

    /**
     * 设置数据状态，防止重复数据操作
     * @param $key
     * @param int $timeout
     * @return bool
     */
    public function set_data_status($key, $timeout = 60)
    {
        return Redis::set($key, 1, ["EX" => $timeout]);
    }

    /**
     * 返回结果
     * @param string $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    public static function returnRst(string $code, string $msg, $data = [])
    {
        if (empty($data)){
            $data = (object)[];
        }
        $res = [
            'code' => $code,
            'msg' => $msg,
            'body' => $data
        ];
        return $res;
    }

    /**
     * 保留几位小数  并且不四舍五入
     * @param $price
     * @return string
     */
    public static function decimalValidate($price){
        if( bcsub($price, 20 , 8) >= 0){
            return bcadd(0, $price, 2);  //       >=10//       >=10<30
        }elseif( (bcsub($price, 20 , 8) < 0) && (bcsub($price, 0.3 , 8) >= 0) ){
            return bcadd(0, $price, 4);  //  0.01 <= a < 1
        }elseif( (bcsub($price, 0.3 , 8) < 0) && (bcsub($price, 0.002 , 8) >= 0) ){
            return bcadd(0, $price, 6);  // 0.001 <= a < 0.01
        }elseif( bcsub($price, 0.002 , 8) < 0 ){
            return bcadd(0, $price, 8);  //       < 0.001
        }
        return $price;
    }

    /**
     * 获取分组id
     * @param $sort_time
     * @param $interval
     * @return float
     */
    public static function get_group_id($sort_time, $interval)
    {
        if($interval == 86400 || $interval == 21600) {
            //由于 UTC+8 的原因，日线开始区间需要特殊处理
            $sort_time += 28800;
        }elseif($interval == 7*24*3600){
            $sort_time += 288000;
        }
        return floor($sort_time / $interval);
    }

    /**
     * 容量换算
     * @param int $capacity 容量
     * @return string
     * */
    public static function capacity_conversion($capacity)
    {
        $c = '';
        if ($capacity < pow(1024, 1)) {
            $c = $capacity . ' B/天';
        } elseif (pow(1024, 1) <= $capacity && $capacity < pow(1024, 2)) {
            $c = bcdiv($capacity, pow(1024, 1), 2) . ' KiB/天';
        } elseif (pow(1024, 2) <= $capacity && $capacity < pow(1024, 3)) {
            $c = bcdiv($capacity, pow(1024, 2), 2) . ' MiB/天';
        } elseif (pow(1024, 3) <= $capacity && $capacity < pow(1024, 4)) {
            $c = bcdiv($capacity, pow(1024, 3), 2) . ' GiB/天';
        } elseif (pow(1024, 4) <= $capacity && $capacity < pow(1024, 5)) {
            $c = bcdiv($capacity, pow(1024, 4), 2) . ' TiB/天';
        } elseif (pow(1024, 5) <= $capacity && $capacity < pow(1024, 6)) {
            $c = bcdiv($capacity, pow(1024, 5), 2) . ' PiB/天';
        } else {
            $c = $capacity . ' B/天';
        }
        return $c;
    }





}
