<?php

namespace App\Lib;

use App\Model\Entity\GoogleSecret;
use App\Model\Entity\UserBasicalInfo;
use App\Rpc\Lib\UserInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use App\Lib\MyCommon;
use Swoft\Bean\BeanFactory;
use Swoft\Redis\Redis;
use App\Lib\MyAuth;
use App\Lib\MyGA;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;

/**
 * 自定义验证规则
 * Class MyValidator
 * @package App\Lib
 *
 * @Bean("MyValidator")
 */
class MyValidator {

    /**
     * @Inject()
     * @var MyCommon
     */
    private $myCommon;

    /**
     * @Inject()
     * @var MyAuth
     */
    private $myAuth;


	public function __construct()
	{

	}

    /**
     * 校验账号类型
     *
     * @param string $value
     * @param string $area_code
     * @return bool|string
     */
	public function account_check(string $value, string $area_code = '80')
	{
        if ($this->myCommon->is_email($value)) {
            return 'email';
        }
        if ($this->myCommon->is_mobile($value, $area_code)) {
            return 'mobile';
        }
        return false;
	}

    /**
     * 检测账号可用性
     * 用于注册类验证，账号存在则返回FALSE
     *
     * @param string $account
     * @param string $account_type
     * @return bool
     */
    public function account_exists(string $account, string $account_type): bool
    {
        $where = [$account_type => $account];
        $user_data = UserBasicalInfo::select('id')->where($where)->first();
        return !empty($user_data);
    }

    /**
     * 手机/邮箱验证码校验
     *
     * @param string $account
     * @param string $code
     * @param string $action
     * @return bool
     */
	public function code_verify(string $account, string $code, string $action): bool
	{
        $code_key = $action. '_code_key';
        $data = MyRedisHelper::hget($code_key, $account);
        if (empty($data))
        {
            return false;
        }
        if (time() > $data['create_time'] + (config('app.code_expire_time', 15)*60)){
            Redis::hDel($code_key, $account);
            return false;
        }
        if ($code != $data['code']){
            return false;
        }
        return Redis::hDel($code_key, $account);
	}

    /**
     * @param string $account
     * @param string $login_pwd
     * @return bool|mixed|object|\Swoft\Db\Eloquent\Builder|\Swoft\Db\Eloquent\Model|null
     * @throws \Swoft\Db\Exception\DbException
     */
	public function account_verify(string $account, string $login_pwd)
    {
        $account_type = $this->account_check($account);
        if (!$account_type){
            return false;
        }
        $user_info = UserBasicalInfo::select('id', 'mobile', 'email', 'login_pwd', 'invite_id', 'google_auth', 'area_code', 'salt')->where([$account_type => $account])->first();
        if (!$user_info){
            return false;
        }
        $verify_pwd = $this->myAuth->password_auth($login_pwd, $user_info['salt'], $user_info['login_pwd']);
        if ($verify_pwd === false){
            return $user_info['id'];
        }
        return $user_info;
    }

    /**
     * 验证谷歌验证码
     * @param string $code
     * @param $uid
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
	public function google_verify(string $code, $uid)
	{
        $secret_data = GoogleSecret::where(['uid' => $uid])->first();
		if (!$secret_data){
		    return false;
        }
		/**@var MyGA $myGA */
		$myGA = BeanFactory::getBean('MyGA');
        return $myGA->verifyCode($secret_data['secret'], $code, 2);
	}

	/**
	 * 重置 验证谷歌验证码
	 */

	public function reset_google_check($code = '', $uid = '')
	{
		$secret = '';

		$reset_google_auth_status = $this->ci->myredis->get('reset_google_auth_' . $uid);

		if(!$reset_google_auth_status)
		{
			return false;
		}

		$secretRst = $this->ci->myredis->get("google_secret_".$uid);
		$secretRst = json_decode($secretRst, true);

		$secret = $secretRst['secret'];

        $checkResult = $this->ci->googleauthenticator->verifyCode($secret, $code, 2);

        return $checkResult;
	}


	/**
	 * 检测证件是否已被使用
	 *
	 * @param [type] $value
	 * @return void
	 */
	public function cert_number_exists($value)
	{
		$post = $this->ci->input->post();
		$clauses = array(
			'where' => array(
				'identity_type_id' => $post['cert_type'],
				'identity_number' => $post['cert_number'],
				'real_name' => $post['real_name'],
			)
		);
		$result = $this->ci->base_model->get_row('user_basical_info', $clauses);
		return empty($result);
	}

	/**
	 * 检测证件是否已被使用 新的方法，从identity_request_log验证身份证号码是否使用
	 *
	 * @param [type] $value
	 * @return void
	 */
	public function cert_number_exists_new($value)
	{
		$post = $this->ci->input->post();
		$clauses = array(
			'where' => array(
				'identity_number' => $post['cert_number'],
				'status <' => 2,
			),
			'order_by' => 'id'
		);
		$result = $this->ci->base_model->get_row('identity_request_log', $clauses);
		return empty($result);
	}


	/**
	 * 检测钱包地址的真实性
	 *
	 * @param [type] $value
	 * @return void
	 */
	public function address_exists($value)
	{
		$this->ci->load->model('core/cold_wallet_model', 'cold');
		return ! empty($this->ci->cold->getOne($value, $this->ci->input->get_post('coin_type')));
	}



	/**
	 * 检查实名认证类型
	 *
	 * @param [type] $value
	 * @return void
	 */
	public function check_cert_type($value)
	{
		$clauses = array(
            'where' => array(
                'type_code' => $value
            ),
            'select' => 'id'
        );
        $type = $this->ci->base_model->get_row('identity_type', $clauses);
        if (empty($type)) {
			return FALSE;
		}
		return TRUE;
	}
}
