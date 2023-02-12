<?php

namespace App\Http\Controller\Api;

use App\lib\MyCode;
use App\Lib\MyCommon;
use App\Lib\MyQuit;
use App\Model\Data\ConfigData;
use App\Model\Data\PaginationData;
use App\Model\Entity\ArticleToUser;
use App\Rpc\Service\KlineService;
use Swoft\Db\DB;
use Swoft\Db\Exception\DbException;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;
use Swoft\Http\Server\Annotation\Mapping\Middlewares;
use App\Http\Middleware\AuthMiddleware;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use Swoft\Bean\Annotation\Mapping\Inject;
use App\Validator\CalcValidator;
use Swoft\Validator\Annotation\Mapping\Validate;

/**
 * Class ArticleController
 * @package App\Http\Controller\Api
 * @Controller(prefix="/v1/article")
 * @Middlewares({
 * })
 */
class ArticleController
{
    /**
     * 文章类型列表
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function type_list(Request $request)
    {
        $where = [
            'status'         => 1,
            'display_status' => 1
        ];
        $data = DB::table('article_type')
            ->select('type_code', 'type_name')
            ->where($where)
            ->orderBy('order_num', 'desc')
            ->get()
            ->toArray();
        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 文章列表
     * @param Request $request
     * @return array
     * @Validate(validator="ArticleValidator",fields={"type"})
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function list(Request $request)
    {
        $params = $request->get();
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? config('page_num');
        $where = [
            "type"   => $params["type"],
            'status' => 1
        ];
        $data = PaginationData::table('article')
            ->select('id', 'title', 'thumbnail', 'summary', 'type')
            ->where($where)
            ->forPage($page, $size)
            ->orderBy('order_num', 'desc')
            ->get();
        foreach ($data['data'] as $key => $val) {
            $data['data'][$key]['thumbnail'] = MyCommon::get_filepath($val['thumbnail']);
        }

        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }

    /**
     * 文章详情
     * @param Request $request
     * @return array
     * @Validate(validator="ArticleValidator",fields={"id"})
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Validator\Exception\ValidatorException
     * @RequestMapping(method={RequestMethod::GET})
     */
    public function details(Request $request)
    {
        $params = $request->get();
        if (!$params["id"]) {
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, '请传入文章ID');
        }
        $data = DB::table('article')->where(['status' => 1, 'id' => $params['id']])->firstArray();
        if (empty($data)) {
            return MyQuit::returnMessage(MyCode::SERVER_ERROR, '未找到此文章');
        }
        //上一篇、下一篇
        $previous_id = DB::table('article')
            ->where(['type' => $data['type'], 'status' => 1, ['order_num', '>=', $data['order_num']], ['id', '<>', $params['id']]])
            ->orderBy('order_num')
            ->value('id');
        $next_id = DB::table('article')
            ->where(['type' => $data['type'], 'status' => 1, ['order_num', '<=', $data['order_num']], ['id', '<>', $params['id']]])
            ->orderBy('order_num', 'desc')
            ->value('id');
        $data['previous'] = $previous_id;
        $data['next'] = $next_id;

        return MyQuit::returnSuccess($data, MyCode::SUCCESS, 'success');
    }
}
