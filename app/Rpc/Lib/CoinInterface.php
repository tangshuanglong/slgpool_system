<?php

namespace App\Rpc\Lib;

/**
 * Interface CoinInterface
 * @package App\Lib
 */
interface CoinInterface
{

    /**
     * @param string $coin_type
     * @return mixed
     */
    public function get_coin_id(string $coin_type);

    /**
     * 获取允许交易的币种列表
     *
     * @return array
     */
    public function get_allow_exchange_type_list();

}
