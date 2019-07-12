<?php

namespace App\Http\Middleware;

use Closure;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $custom_appid = $request->header('X-API-KEY');
        $timestamp = $request->header('X-API-TIMESTAMP');
        $nonstr = $request->header('X-API-NONSTR');
        $accept = $request->header('Accept');
        $signature = $request->header('X-API-SIGNATURE');
        if (!$custom_appid || !$timestamp || !$nonstr || !$accept || !$signature) {
            return response(['code' => 400, 'message' => '非法请求']);
        }
        $custom_appkey = $this->_getCustomAppkey($custom_appid);
        if (!$custom_appkey) {
            return response(['code' => 400, 'message' => '未知用户']);
        }

        $data = [
            'X-API-KEY' => $custom_appid,
            'X-API-TIMESTAMP' => $timestamp,
            'X-API-NONSTR' => $nonstr,
            'Accept' => $accept,
        ];

        $stringTemp = $this->_getSignContent($data) . '&key=' . $custom_appkey;

        $signature_new = strtoupper(hash_hmac('sha256', $stringTemp, $custom_appkey));

        if ($signature != $signature_new) {
            return response(['code' => 400, 'message' => '签名错误'.$signature_new]);
        }
        return $next($request);
    }

    private function _getCustomAppkey($custom_appid)
    {
        $id_arr = env('CUSTOM_APPID');
        $key_arr = env('CUSTOM_APPKEY');
        if ( empty($id_arr) || empty($key_arr) ){
            return false;
        }
        $id_arr = explode(',',$id_arr);
        $key_arr = explode(',',$key_arr);
        if ( !in_array($custom_appid,$id_arr) ){
            return false;
        }
        $key = array_search($custom_appid,$id_arr);
        return isset($key_arr[$key]) ? $key_arr[$key] : false;
    }

    private function _getSignContent($x_api_params)
    {
        ksort($x_api_params);

        $sign_data = [];
        foreach ($x_api_params as $k => $v) {
            if (!$this->_checkEmpty($v) && "@" != substr($v, 0, 1)) {
                $sign_data[] = $k . '=' . $v;
            }
        }

        return implode('&', $sign_data);
    }

    /**
     * @description 检测数据是否为空
     *
     * @param $item
     * @return bool
     */
    private function _checkEmpty($item)
    {
        return empty($item) || (trim($item) === '') || ($item === null);
    }
}
