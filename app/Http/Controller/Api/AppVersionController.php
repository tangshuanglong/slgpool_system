<?php

namespace App\Http\Controller\Api;

use App\Lib\MyCode;
use App\Lib\MyQuit;
use Swoft\Db\DB;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use App\Http\Middleware\BaseMiddleware;

/**
 * Class AppVersionController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/app_version")
 * @Middleware(BaseMiddleware::class)
 */
class AppVersionController
{
    /**
     * 版本更新接口
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function update(Request $request)
    {
        $params = $request->params;
        validate($params, 'AppVersionValidator');
        $where               = [
            ['type', '=', $params['type']],
            ['version', '>', $params['version']],
        ];
        $version             = DB::table('app_version')
            ->select('version', 'url', 'is_force', 'remark')
            ->where($where)
            ->orderByDesc('id')
            ->limit(1)
            ->firstArray();
        $data['update_flag'] = 0;//最新版本
        $data['info']        = [];
        if ($version) {
            $data['update_flag'] = 1;//不是最新，需要更新
            $data['info']        = $version;
        }
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }
}
