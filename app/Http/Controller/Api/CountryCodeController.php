<?php

namespace App\Http\Controller\Api;

use App\Lib\MyCode;
use App\Lib\MyCommon;
use App\Lib\MyIP;
use App\Lib\MyQuit;
use App\Model\Entity\CountryCode;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use App\Http\Middleware\BaseMiddleware;

/**
 * Class CountryCodeController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/country_code")
 * @Middleware(BaseMiddleware::class)
 */
class CountryCodeController
{

    /**
     * 国家信息列表
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function list()
    {
        $data = CountryCode::all()->toArray();
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, '成功');
    }

    /**
     * 根据请求的客户端ip查询对应的国家
     * @param Request $request
     * @return array
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function get_country(Request $request)
    {
        $ip = MyCommon::get_ip($request);
        $myIp = new MyIP();
        $ip_info = $myIp->find($ip);
        return MyQuit::returnSuccess(['country_name' => $ip_info[0]], MyCode::SUCCESS, '成功');
    }

}
