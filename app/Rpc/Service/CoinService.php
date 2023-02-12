<?php

namespace App\Rpc\Service;

use App\Model\Data\CoinData;
use App\Model\Data\ConfigData;
use App\Rpc\Lib\CoinInterface;
use Swoft\Db\DB;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

/**
 * Class CoinService
 * @package App\Rpc\Service
 * @Service()
 */
class CoinService implements CoinInterface {


    /**
     * 获取币种id
     * @param string $coin_type
     * @return bool|mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_coin_id(string $coin_type)
    {
        $data = CoinData::get_coin($coin_type);
        if ($data) {
            return $data['id'];
        }
        return false;
    }

    /**
     * @param string $coin_type
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_coin_info(string $coin_type)
    {
        return CoinData::get_coin($coin_type);
    }

    /**
     * 获取所有币种名称
     * @return bool | array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_all_coin_name()
    {
        $data = DB::table('coin')
            ->select('id', 'coin_name_en', 'coin_icon', 'charge_status', 'get_cash_status', 'exchange_enable')
            ->where(['show_flag' => 1])->get()->toArray();
        if ($data) {
            return $data;
        }
        return false;
    }

    /**
     * 获取公链信息
     * @param string $chain_name
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_chain_info(string $chain_name)
    {
        return DB::table('chain')->where(['chain_name' => $chain_name, 'cancel_flag' => 0])->firstArray();
    }

    /**
     * 获取token信息
     * @param $coin_id
     * @param $chain_id
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_token_info($coin_id, $chain_id)
    {
        return DB::table('coin_token')->where(['coin_id' => $coin_id, 'chain_id' => $chain_id, 'cancel_flag' => 0])->firstArray();
    }

    /**
     * 判断公链是否存在
     * @param string $chain_name
     * @return bool
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function chain_exists(string $chain_name)
    {
        return DB::table('chain')->where(['chain_name' => $chain_name, 'cancel_flag' => 0])->exists();
    }

    /**
     * 获取所有抵币借贷币种
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function loan_coins()
    {
        return CoinData::loan_coins();
    }

    /**
     * 获取抵币借贷币种信息
     * @param int $coin_id
     * @return object|\Swoft\Db\Eloquent\Model|\Swoft\Db\Query\Builder|null
     * @throws \Swoft\Db\Exception\DbException
     */
    public function loan_coin(int $coin_id)
    {
        return CoinData::loan_coin($coin_id);
    }

    /**
     * 获取币种最新价格
     * @param string $coin_type
     * @param string $price_type
     * @return string
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_coin_last_price(string $coin_type, string $price_type)
    {
        if ($coin_type === $price_type) {
            return '1';
        }
        return CoinData::get_coin_last_price($coin_type, $price_type);
    }

    /**
     * 获取币种的所有token信息
     * @param string $coin_name
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_coin_tokens(string $coin_name)
    {
        return DB::table('coin_token')->where(['coin_name' => strtolower($coin_name), 'cancel_flag' => 0])->get()->toArray();
    }

    /**
     * 获取可支付的币种
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_pay_coins()
    {
        return DB::table('coin')->select('id', 'coin_name_en')->where(['show_flag' => 1, 'pay_enable' => 1])->get()->toArray();
    }

    /**
     * 根据币种id 获取币种名称
     * @param int $coin_id
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_coin_name(int $coin_id)
    {
        return DB::table('coin')->select('coin_name_en')->where(['id' => $coin_id])->firstArray();
    }

    /**
     * 获取允许交易的币种列表
     *
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_allow_exchange_type_list()
    {
        // 获取允许交易的coinType列表
        $coinList = DB::table('coin')->where(['exchange_enable' => 1])->get()->toArray();
        $coinTypeList = array_column($coinList, 'coin_name_en');

        // 获取允许交易的priceType列表
        [$priceTypeListJson] = ConfigData::getConfigValue('system', 'price_type_list');
        $priceTypeList = json_decode($priceTypeListJson, true);

        $data = [
            'coin_type_list' => $coinTypeList,
            'price_type_list' => $priceTypeList
        ];

        return $data;
    }


}
