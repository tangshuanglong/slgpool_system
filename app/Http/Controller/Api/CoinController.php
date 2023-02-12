<?php

namespace App\Http\Controller\Api;

use App\lib\MyCode;
use App\Lib\MyCommon;
use App\Lib\MyQuit;
use App\Model\Data\CoinData;
use App\Model\Data\ConfigData;
use App\Rpc\Service\CoinService;
use App\Rpc\Service\KlineService;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Db\DB;
use Swoft\Db\Exception\DbException;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use App\Http\Middleware\BaseMiddleware;
use Swoft\Redis\Redis;

/**
 * Class CoinController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/coin")
 * @Middleware(BaseMiddleware::class)
 */
class CoinController
{

    /**
     * @Inject()
     * @var MyCommon
     */
    private $myCommon;

    /**
     * @Inject()
     * @var CoinService
     */
    private $coinService;

    /**
     * @Inject()
     * @var KlineService
     */
    private $klineService;

    /**
     * 获取币种列表
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function coin_names(Request $request)
    {
        $params = $request->params;
        validate($params, 'CoinValidator', ['type']);
        $data = CoinData::coin_names($params['type']);
        foreach ($data as $key => $val) {
            if (isset($params['type']) && in_array($params['type'], ['withdraw', 'deposit'])) {
                if ($val['coin_name_en'] === 'cny' && ($params['type'])) {
                    unset($data[$key]);
                    continue;
                }
            }
            $data[$key]['coin_type'] = strtoupper($val['coin_name_en']);
        }
        $data = array_values($data);
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 获取首页行情，区块信息【暂时没用】
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function block_stats()
    {
        //获取区块统计信息，当前只获取btc的
        $block_stats = DB::table('coin_block_stats')->select('coin_type', 'difficulty',
            'next_difficulty')->where(['coin_type' => 'btc'])->firstArray();
        $block_stats['last_price'] = '0';
        $block_stats['last_price_cny'] = '0';
        $block_stats['rate'] = 0;
        $block_stats['symbol'] = 'BTC/CNY';
        if ($block_stats) {
            $block_stats['difficulty'] = round($block_stats['difficulty'] / 1000000000000, 2);
            $block_stats['next_difficulty'] = round($block_stats['next_difficulty'] / 1000000000000, 2);
            //币种的最新价格
            $kline = CoinData::get_kline($block_stats['coin_type'], 'usdt');
            $block_stats['last_price'] = MyCommon::decimalValidate($kline['close_price']);
            $usdt_cny = CoinData::get_coin_last_price('usdt', 'cny');
            $block_stats['last_price_cny'] = bcmul($kline['close_price'], $usdt_cny, 2);
            //获取昨日收盘价
            $yesterday_close_price = CoinData::get_yesterday_close_price($block_stats['coin_type'], 'usdt');
            $block_stats['rate'] = round(($kline['close_price'] - $yesterday_close_price) / $yesterday_close_price * 100,
                2);
        }
        return MyQuit::returnSuccess($block_stats, MyCode::SUCCESS, 'success');
    }

    /**
     * 获取链信息
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function get_chains(Request $request)
    {
        $params = $request->params;
        validate($params, 'CoinValidator', ['coin_type']);
        $data = CoinData::get_chains($params['coin_type']);
        foreach ($data as $key => $val) {
            $data[$key]['display_name'] = strtoupper($val['display_name']);
        }
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    private function get_left_right($coinType, $priceType)
    {
        $right_table_arr = [
            "filusdt" => '',
            "filcny"  => '',
            "usdtcny" => '',
        ];

        $right_table = [
            "usdt" => [
                'cny',
                'fil',
            ],
            "cny"  => [
                'fil',
                'usdt',
            ],
            'fil'  => [
                'usdt',
                'cny',
            ]
        ];

        $coinLeft = "";
        $coinRight = "";

        if (!isset($right_table[$coinType])) {
            throw new DbException('不提供此种类型的闪兑');
            //return MyQuit::returnMessage(MyCode::SERVER_ERROR, '不提供此种类型的闪兑');
        } else {
            foreach ($right_table[$coinType] as $v) {
                if ($v == $priceType) {
                    $table_name = $coinType . $priceType;
                    if (!array_key_exists($table_name, $right_table_arr)) {
                        $table_name = $priceType . $coinType;
                        if (!array_key_exists($table_name, $right_table_arr)) {
                            throw new DbException('不提供此种类型的闪兑');
                            //return MyQuit::returnMessage(MyCode::SERVER_ERROR, '不提供此种类型的闪兑');
                        } else {

                            $coinLeft = $priceType;
                            $coinRight = $coinType;
                            break;
                        }
                    } else {

                        $coinLeft = $coinType;
                        $coinRight = $priceType;
                        break;
                    }
                }
            }
        }

        return [$coinLeft, $coinRight];
    }

    /**
     * 允许闪兑交易的币种列表信息【暂时没用】
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function get_allow_exchange_type_list(Request $request)
    {
        $data = $this->coinService->get_allow_exchange_type_list();

        echo var_dump($data);

        //获取兑换人民币价格
        foreach ($data['coin_type_list'] as $key => $coin) {
            // 对接usdt价格
            if ($coin === 'bsf') {
                $usdtPrice = 1;
            } else {
                [$coinLeft, $coinRight] = $this->get_left_right($coin, "ustd");
                $usdtPrice = $this->klineService->get_last_close_price($coinLeft, $coinRight);
            }

            // 获取到usdt对cny的价格，计算出当前coinType对cny的价格
            $usdtCnyPrice = $this->klineService->get_last_close_price('usdt', 'cny');
            $cnyPrice = bcmul($usdtPrice, $usdtCnyPrice, 2);

            //获取对应id
            $id = $this->coinService->get_coin_id($coin);

            $data['coin_type_list'][$key] = [
                'id'        => $id,
                'coin_type' => $coin,
                'coin_usdt' => $usdtPrice,
                'coin_cny'  => $cnyPrice
            ];
        }

        // priceType 需要兑换人民币价格
        foreach ($data['price_type_list'] as $key => $price) {
            // 对接usdt价格
            if ($price === 'bsf') {
                $usdtPrice = 1;
            } else {
                [$coinLeft, $coinRight] = get_left_right_coin($price, "ustd");
                $usdtPrice = $this->klineService->get_last_close_price($coinLeft, $coinRight);
            }

            // 获取到usdt对cny的价格，计算出当前coinType对cny的价格
            $usdtCnyPrice = $this->klineService->get_last_close_price('usdt', 'cny');
            $cnyPrice = bcmul($usdtPrice, $usdtCnyPrice, 2);

            //获取对应id
            $id = $this->coinService->get_coin_id($price);

            $data['price_type_list'][$key] = [
                'id'         => $id,
                'price_type' => $price,
                'coin_cny'   => $cnyPrice
            ];
        }

        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 获取闪兑交易的实时兑换数量【暂时没用】
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function get_exchange_price(Request $request)
    {
        $params = $request->params;
        $coinType = $params['coin_type'] ?? '';
        $priceType = $params['price_type'] ?? '';
        [$coinLeft, $coinRight] = get_left_right_coin($coinType, $priceType);
        $price = $this->klineService->get_last_close_price($coinLeft, $coinRight);
        // 获取最新价格
        $data = ['price' => $price];
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 首页矿场数据
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function statistic()
    {
        $redis_key = "index:fil:data";
        $data = Redis::hGetAll($redis_key);
        foreach ($data as $key => $item) {
            //去掉$
            $tmp = explode('$', $item);
            if (isset($tmp[1])) {
                $item = $tmp[1];
            }
            //去掉单位
            $tmp = explode(' ', $item);
            $data[$key] = $tmp[0] ?? 0;
        }
        //封存质押(FIL/32GiB)
        $pledge_32g = Redis::hGet('index:fil:data', 'sectorInitialPledge');
        //$pledge_1T = bcmul($pledge_32g, 32, 6);
        $data['seal_pledge'] = $pledge_32g . ' FIL/32GiB';
        //新增GAS消耗(FIL/32GiB)
        $preCommitSector = Redis::hGet('fil:fee:data', 'PreCommitSector');
        $proveCommitSector = Redis::hGet('fil:fee:data', 'ProveCommitSector');
        $submitWindowedPoSt = Redis::hGet('fil:fee:data', 'SubmitWindowedPoSt');
        $preCommitSector = bcdiv($preCommitSector, pow(10, 18), 8);
        $proveCommitSector = bcdiv($proveCommitSector, pow(10, 18), 8);
        $submitWindowedPoSt = bcdiv($submitWindowedPoSt, pow(10, 18), 8);
        //$inc_fee = ($preCommitSector + $proveCommitSector) * 32;
        $inc_fee = $preCommitSector + $proveCommitSector;
        $keep_fee = 32 / 2349 * $submitWindowedPoSt;
        $total_fee = bcadd($inc_fee, $keep_fee, 4);
        $data['add_gas'] = $total_fee . ' FIL/32GiB';
        //挖矿效率(FIL/TB)、算力增速(FIL/TB)、出块数量
        $node_num = 'f0113735';
        $group_id = MyCommon::get_group_id(time(), 86400);
        $miner = DB::table('miner')->select('raw_byte_power_growth', 'avg_fil', 'block_number')
            ->where(['node_num' => $node_num, 'group_id' => $group_id])->first();
        $data['raw_byte_power_growth'] = MyCommon::capacity_conversion($miner['raw_byte_power_growth']);
        $data['avg_fil'] = sprintf("%.4f",  $miner['avg_fil']) . ' FIL/TiB';
        $data['block_number'] = $miner['block_number'];
        $data['fil_miner'] = 'https://filfox.info';
        $data['slgpool_node'] = 'https://filfox.info/en/address/' . $node_num;

        //bzz的区块链统计信息
        $redis_key = "index:bzz:data";
        $bzz_stats = Redis::hGetAll($redis_key);
        $data['bzz_stats'] = $bzz_stats;
        $bzz_config_data = ConfigData::config_info_group('bzz_stats');
        $data['bzz_stats']['slg_nodes_all'] = 0;
        $data['bzz_stats']['24h_slg_cheque_cashed'] = 0;
        $data['bzz_stats']['bzz_url'] = 'http://bzzscan.com/';
        foreach ($bzz_config_data as $val) {
            $data['bzz_stats'][$val['name']] = $val['value'];
        }
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * k线数据【暂时没用】
     * @param Request $request
     * @return array
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function klines(Request $request)
    {
        $params = $request->params;
        if (empty($params)) {
            return MyQuit::returnMessage(MyCode::LACK_PARAM, '缺乏参数');
        }
        validate($params, 'LoanValidator', ['symbol']);
        $table_name = 'kline_'.strtolower($params['symbol']).'_86400';
        $sql = "select `table_name` from `information_schema`.`TABLES` where `table_name` = 'bt_{$table_name}'";
        if (empty(DB::selectOne($sql))) {
            return MyQuit::returnMessage(MyCode::PARAM_ERROR, '符号错误');
        }
        //默认取一百条数据
        $end_time = strtotime(date("Y-m-d H:00:00"));
        if (isset($params['end_time'])) {
            if (!is_numeric($end_time) || strlen($params['end_time']) > 10) {
                return MyQuit::returnMessage(MyCode::PARAM_ERROR, '结束时间错误');
            }
            $end_time = $params['end_time'];
        }
        $start_time = $end_time - 86400*100;
        $where = [
            ['sort_time', '>', $start_time],
            ['sort_time', '<=', $end_time]
        ];
        $data = DB::table($table_name)->where($where)->orderBy('group_id', 'desc')->get();
        $res = [];
        foreach ($data as $val) {
            $res[] = [
                $val['sort_time'],
                $val['open_price'],
                $val['high_price'],
                $val['low_price'],
                $val['close_price'],
                $val['amount'],
            ];
        }
        return MyQuit::returnSuccess($res, MyCode::SUCCESS, 'success');
    }
}
