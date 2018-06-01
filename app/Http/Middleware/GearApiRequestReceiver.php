<?php

namespace App\Http\Middleware;

use Closure;

/**
 * 请求入口处处理，任务包含：
 * 1. 记录请求数据包
 * 2. 实例化好gearman client,并全局单例化
 * 3. 生成全局的识别码uuid，并全局可获取化
 * 
 * @author Administrator
 *
 */
class GearApiRequestReceiver
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
        
        $ga_traceno = create_uuid();
        
        app()->singleton('ga_traceno', function($app) use ($ga_traceno){
            return $ga_traceno;
        });

        app('galog')->log(json_encode([
            'data' => $data, 
            'sign' => $sign
        ]), 'landingtouch', 'Req');

        return $next($request);
    }
}
