<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\InterfaceConfig;

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
        
        $signverifyRet = app('gclient')->doNormal(InterfaceConfig::BIZ_TYPES['sign.verify'], json_encode([
            'data' => $dataOri,
            'sign' => $sign,
            'ga_traceno' => app('ga_traceno')
        ])); 
        
        $signverifyRetDe = json_decode($signverifyRet, true);
        if(isset($signverifyRetDe['data'])){
            $signData = json_decode($signverifyRetDe['data'], true);
            if(isset($signData['code']) && $signData['code'] == FormatResultErrors::CODE_MAP['SUCCESS']['code']){
                $request['mch_md5_token'] = $signData['biz_content']['mch_md5_token'];
                return $next($request);
            }
        }
        
        return $signverifyRet;
    }
}
