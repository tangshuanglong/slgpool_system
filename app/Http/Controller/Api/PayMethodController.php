<?php
namespace App\Http\Controller\Api;

use App\Http\Middleware\AuthMiddleware;
use App\Lib\MyCode;
use App\Lib\MyQuit;
use App\Model\Data\CoinData;
use App\Rpc\Lib\WalletDwInterface;
use Swoft\Db\DB;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;

/**
 * Class PayMethodController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/pay_method")
 * @Middleware(AuthMiddleware::class)
 *
 *
 */
class PayMethodController
{
    /**
     * @Reference(pool="user.pool")
     * @var WalletDwInterface
     */
    private $walletDwServer;

    /**
     * 获取支付方式
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @RequestMapping(method={RequestMethod::GET})
     */
    function list(Request $request)
    {
        $params = $request->get();
        $query = DB::table('pay_method');
        if(isset($params['pay_name']) && $params['pay_name'] && strpos($params['pay_name'], ',')){
            $query->whereIn('pay_name', explode(',', $params['pay_name']));
        }elseif(isset($params['pay_name']) && $params['pay_name']){
            $query->where('pay_name', $params['pay_name']);
        }
        $data = $query->get()->toArray();
        foreach ($data as $key => $value) {
            if ($value['type'] === 1) {
                $coin_data_info = CoinData::get_coin_info($value['coin_id']);
                $data[$key]['coin_icon'] = $coin_data_info['coin_icon'];
                //获取用户对应币种的可用余额
                $data[$key]['free_coin_amount'] = $this->walletDwServer->get_wallet_free($request->uid, $value['coin_id']);
            }
        }
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

}
