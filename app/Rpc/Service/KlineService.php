<?php

namespace App\Rpc\Service;

use App\Lib\MyCommon;
use App\Model\Data\CoinData;
use App\Rpc\Lib\KlineInterface;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Db\DB;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

/**
 * Class KlineService
 * @package App\Rpc\Service
 * @Service()
 */
class KlineService implements KlineInterface {

    /**
     * @Inject()
     * @var MyCommon
     */
    private $myCommon;

    /**
     * @param string $coin_name
     * @param string $quote_name
     * @return int|mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_last_close_price(string $coin_name, string $quote_name)
    {
        if ($coin_name === $quote_name) {
            return '1';
        }

        try{
            $price = CoinData::get_coin_last_price($coin_name, $quote_name);
            if ($price == 0){
                $price = CoinData::get_coin_last_price($quote_name,$coin_name);
            }

            if ($price >0){
                return MyCommon::decimalValidate($price);
            }else{
                return MyCommon::decimalValidate(0);
            }
        } catch (\Exception $e){
            return MyCommon::decimalValidate(0);
        }
    }
}
