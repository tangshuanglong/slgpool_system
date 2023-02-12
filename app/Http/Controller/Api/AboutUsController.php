<?php

namespace App\Http\Controller\Api;

use App\lib\MyCode;
use App\Lib\MyQuit;
use App\Model\Data\ConfigData;
use Swoft\Db\DB;
use Swoft\Db\Exception\DbException;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\Middlewares;
use App\Http\Middleware\AuthMiddleware;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;

/**
 * Class AboutUsController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/about_us")
 * @Middlewares({
 * })
 */
class AboutUsController
{
    /**
     * 关于我们-联系我们
     * @param $id
     * @return array
     * @throws DbException
     * @RequestMapping(method={RequestMethod::GET}, route="contact_us")
     */
    public function contact_us()
    {
        [$config] = ConfigData::getConfigValue('system', 'company_about_us');
        $config_list = json_decode($config, true);
        $data = [
            'phone'   => $config_list[0],
            'wechat'  => $config_list[1],
            'email'   => $config_list[2],
            'website' => $config_list[3],
            'address' => $config_list[4]
        ];
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 关于我们-矿机视频列表
     * @param $id
     * @return array
     * @throws DbException
     * @RequestMapping(method={RequestMethod::GET}, route="mining_site_video")
     */
    public function mining_site_video()
    {
        $data = DB::table('mining_site_video')->where(['status' => 1])->orderBy('sort')->get()->toArray();
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }
}
