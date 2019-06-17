<?php

namespace App\Http\Middleware;

use Closure;

/**
 * 请求入口处处理，任务包含：
 * 1. 记录请求数据包
 * 2. 生成api_traceno并全局化保存
 * 
 * @author Administrator
 *
 */
class ApiRequestReceiver
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $data = $request->input('data', '');
        $sign = $request->input('sign', '');
        
        $api_traceno = create_uuid();
        app()->singleton('api_traceno', function($app) use ($api_traceno){
            return $api_traceno;
        });

        app('apilog')->log(json_encode([
            'data' => $data, 
            'sign' => $sign
        ]), 'landingtouch', 'Req');
        
        return $next($request);
    }
}
