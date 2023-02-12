<?php

namespace App\Http\Controller\Api;

use App\Lib\MyCode;
use App\Lib\MyCommon;
use App\Lib\MyQuit;
use Swoft\Db\DB;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use App\Http\Middleware\BaseMiddleware;

/**
 * Class BannerController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/banner")
 * @Middleware(BaseMiddleware::class)
 */
class BannerController
{

    /**
     * 获取轮播图列表
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function list(Request $request)
    {
        $params = $request->params;
        $params = validate($params, 'BannerValidator', ['use_client', 'position']);
        $params['position'] = isset($params['position']) ? $params['position'] : 'index';
        $data = DB::table('img_banner')->select('img_src', 'type', 'scenes_type', 'link_href', 'content')
            ->where(['use_client' => $params['use_client'], 'position' => $params['position'], 'status' => 1])
            ->orderBy('sort_num', 'desc')->get()->toArray();
        foreach ($data as $key => $val) {
            $data[$key]['img_src'] = MyCommon::get_filepath($val['img_src']);
            if ($val['link_href'] && $val['type'] === 2) {
                $data[$key]['link_href'] = MyCommon::get_filepath($val['link_href'], 'file');
            }
        }
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }
}
