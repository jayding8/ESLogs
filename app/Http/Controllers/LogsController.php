<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\Logs;
use Illuminate\Support\Str;
use Elasticsearch\ClientBuilder;

class LogsController extends Controller
{
    const FILLABLE = ['sys_bundle', 'app_bundle', 'module_bundle', 'op', 'op_name', 'user_id', 'user_name', 'create_time', 'analysis'];

    const SORTFIELD = ['_score', 'app_bundle', 'module_bundle', 'op', 'user_id', 'create_time'];

    public $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()->build();
    }

    public function search(Request $request)
    {
        $from = intval($request->get('offset')) ?: 0;
        $size = intval($request->get('count')) ?: 10;

        $sort_field = $request->get('sort_field') ?: '_score';
        if ( !in_array($sort_field,self::SORTFIELD) ){
            return response(['code' => 400, 'message' => 'Error sort field!']);
        }

        $sort_type = strtolower($request->get('sort_type')) ?: 'desc';
        if ( !in_array($sort_type,['asc','desc']) ){
            return response(['code' => 400, 'message' => 'Error sort type!']);
        }

        $search_field = $request->get('search_field');
        $search_value = $request->get('search_value');
        $search_field = explode('@@',$search_field);
        $search_value = explode('@@',$search_value);
        if ( empty($search_field) || empty($search_value) || count($search_field) != count($search_value) ) {
            return response(['code' => 400, 'message' => 'Error Search Params!']);
        }

        $query = [];
        foreach ( $search_field as $key => $v ) {
            if ( !in_array($v,self::FILLABLE) ){
                continue;
            }
            $query['bool']['must'][] = [
                'match' => [
                    $v => [ 'query' => $search_value[$key] ]
                ]

            ];
        }
        if ( empty($query) ){
            return response(['code' => 400, 'message' => 'Illegal Params!']);
        }

        $param = [
            'index' => 'logs',
            'type' => 'logs',
//            'search_type' => 'dfs_query_then_fetch',  //处理相同内容,评分不同的问题,有一定的性能消耗,取决于索引大小,分片数量,查询频率
            'body' => [
                'query' => $query,
                'from' => $from,
                'size' => $size,
                'sort' => [ $sort_field => $sort_type ],
//                "_source"=>[
//                     "excludes"=> [ "op" ]        //过滤返回值中的指定字段
//                ],
            ]
        ];
//        dd($param);
        $res = $this->client->search($param);

        return response(['code' => 200, 'data' => $res['hits']]);
    }

    /**
     * 新增日志
     */
    public function addLog(Request $request)
    {
        $data = $request->only(self::FILLABLE);
        if (!isset($data['sys_bundle']) || !$data['sys_bundle']) {
            return response(['code' => 400, 'message' => '系统标识错误']);
        }
        if (!isset($data['app_bundle']) || !$data['app_bundle']) {
            return response(['code' => 400, 'message' => '应用标识错误']);
        }
        if (!isset($data['module_bundle']) || !$data['module_bundle']) {
            return response(['code' => 400, 'message' => '模块标识错误']);
        }
        if (!isset($data['op']) || !$data['op']) {
            return response(['code' => 400, 'message' => '操作标识错误']);
        }
        $data['ip'] = $request->ip();
        $data['create_time'] = $request->get('create_time') ?? time();
        $es_id = (string)Str::orderedUuid();

        $log_to_redis = (new Logs($es_id, $data))->onQueue('logs_to_redis');
        dispatch($log_to_redis);
        return response(['code' => 200, 'message' => '添加成功', 'id' => $es_id]);

    }

    public function detail(Request $request)
    {
        $id = $request->get('id');
        if (!$id) {
            return response(['code' => 400, 'message' => 'ID不能为空']);
        }
        $param = [
            'index' => 'logs',
            'type' => 'logs',
            'id' => $id,
            'client' => ['ignore' => 404]
        ];
        $res = $this->client->get($param);
        if (!$res['found']) {
            return response(['code' => 400, 'message' => 'ID错误']);
        }
        return response(['code' => 200, 'data' => $res]);
    }

}
