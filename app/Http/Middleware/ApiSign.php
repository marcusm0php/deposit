<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\InterfaceConfig;
use App\Libs\FormatResultErrors;

/**
 * 接口请求验签处理，任务包含：
 * 1. 验签
 * 2. 查询merSign记录并全局化保存
 * 3. 查询merBase记录并全局化保存
 * 4. 日志记录验签结果
 *
 * @author MarcusM
 *
 */
class ApiSign
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
        
        // 验签计算、签名记录查询并透传、商户记录查询并透传
        return $next($request);
    }
}
