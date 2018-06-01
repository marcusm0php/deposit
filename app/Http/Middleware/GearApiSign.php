<?php

namespace App\Http\Middleware;

use Closure;

/**
 * 调用sign.verify处理，任务包含：
 * 1. 发送
 * 2. 实例化好gearman client,并全局单例化
 * 3. 生成全局的识别码uuid，并全局可获取化
 *
 * @author Administrator
 *
 */
class GearApiSign
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
        $dataOri = $request->input('data', '');
        $sign = $request->input('sign', '');
        $data = json_decode($dataOri, true);
        
        $signverifyRet = app('gclient')->doNormal('deposit.sign.verify', json_encode([
            'data' => $dataOri,
            'sign' => $sign,
            'ga_traceno' => app('ga_traceno')
        ])); 
        
        echo $signverifyRet;
        
        return $next($request);
    }
}
