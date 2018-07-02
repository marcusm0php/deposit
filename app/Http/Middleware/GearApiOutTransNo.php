<?php

namespace App\Http\Middleware;

use App\Libs\FormatResultErrors;
use App\Models\InterfaceConfig;
use Closure;

class GearApiOutTransNo
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
        $data = json_decode($dataOri, true);
        $sign = $request->input('sign', '');

        $outtransnoverifyRet = app('gclient')->doNormal(InterfaceConfig::BIZ_TYPES['outtransno.verify'], json_encode([
            'data' => $dataOri,
            'ga_traceno' => app('ga_traceno'),
            'sign' => $sign,
        ]));

        $outtransnoverifyRetDe = json_decode($outtransnoverifyRet, true);

        if(isset($outtransnoverifyRetDe['data'])){
            $data = json_decode($outtransnoverifyRetDe['data'], true);
            if(isset($data['code']) && $data['code'] == FormatResultErrors::CODE_MAP['SUCCESS']['code']){
                return $next($request);
            }
        }
        return $outtransnoverifyRet;
    }
}
