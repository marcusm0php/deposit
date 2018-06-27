<?php

namespace App\Console\Commands;

use App\Libs\Interfaces\SmsInterface;
use App\Models\Bankcard;
use App\Models\Mchsub;
use App\Models\MchAccnt;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Libs\FormatResult;

class GearDepositCommand extends GearCommandBase
{
    use GearCommandGeneralTrait;

    protected $signature = 'command:gear:deposit';
    protected $description = 'Gearman Working: Deposit around functions.';

    //3.1 商户开设子账户
    public function workMchsubcreate($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans)
    {

        if (empty($bizContentFormat['out_mch_accnt_no'])) {
            $this->_formatResult->setError('MCHSUB.CREATE.MCHSUB.OUTMCHACCNTNO.INVALID');
            return $this->_signReturn($this->_formatResult->getData());
        }

        $mchaccnt = \App\Models\MchAccnt::where('out_mch_accnt_no', $bizContentFormat['out_mch_accnt_no'])
            ->where('mch_no', $data['mch_no'])
            ->first();
        if ($mchaccnt) {
            $this->_formatResult->setError('MCHSUB.CREATE.MCHSUB.OUTMCHSUBNO.REPEAT');
            return $this->_signReturn($this->_formatResult->getData());
        }

        $mch_accnt_no = MchAccnt::generateMchAccntNo();
        $mchaccnt = new MchAccnt;
        $mchaccnt->mch_no = $data['mch_no'];
        $mchaccnt->mch_accnt_no = $mch_accnt_no;
        $mchaccnt->out_mch_accnt_no = $bizContentFormat['out_mch_accnt_no'];
        $mchaccnt->accnt_type = MchAccnt::ACCNT_TYPE_MCHSUB;
        $mchaccnt->mch_accnt_name = $bizContentFormat['mch_accnt_name'];
        $mchaccnt->link_name = $bizContentFormat['link_name'];
        $mchaccnt->link_phone = $bizContentFormat['link_phone'];
        $mchaccnt->link_email = $bizContentFormat['link_email'];
        $mchaccnt->save();

        $this->_formatResult->setSuccess([
            'mch_accnt_no' => $mch_accnt_no,
            'out_mch_accnt_no' => $mchaccnt->out_mch_accnt_no
        ]);
        return $this->_signReturn($this->_formatResult->getData());
    }

    //3.2 子商户绑定银行卡-提交资料
    public function workMchsubbindbankcard($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans)
    {

        $mchaccnt = \App\Models\MchAccnt::where('mch_no', $data['mch_no'])
            ->where('mch_accnt_no', $bizContentFormat['mch_accnt_no'])
            ->first();

        if (empty($mchaccnt)) {
            $this->_formatResult->setError('MCHSUB.MCHACCNTNO.INVALID');
            return $this->_signReturn($this->_formatResult->getData());
        }

        $bizContentFormat['card_type'] = in_array($bizContentFormat['card_type'], \App\Models\Bankcard::CARD_TYPE) ? $bizContentFormat['card_type'] : '0';
        $bizContentFormat['card_expire_date'] = date('Y-m-d', strtotime($bizContentFormat['card_expire_date']));

        //TODO call cib_interface
        $bank_card_existed = \App\Models\Bankcard::where('mch_no', $data['mch_no'])
            ->where('mch_accnt_no', $bizContentFormat['mch_accnt_no'])
            ->where('card_no', $bizContentFormat['card_no'])
            ->first();

        if ($bank_card_existed) {
            $this->_formatResult->setError('MCHSUB.CREATE.BANKCARD.REPEAT');
            return $this->_signReturn($this->_formatResult->getData());
        }
        //鉴权
        $auth_data = [
            'trac_no' => uniqid(),
            'card_no' => $bizContentFormat['card_no'],
            'bank_no' => $bizContentFormat['bank_no'],
            'acct_type' => $bizContentFormat['card_type'],
            'cert_type' => $bizContentFormat['cert_type'],
            'cert_no' => $bizContentFormat['cert_no'],
            'card_phone' => $bizContentFormat['card_phone'],
            'expireDate' => $bizContentFormat['card_expire_date'],
            'cvn' => $bizContentFormat['card_cvn'],
            'user_name' => $bizContentFormat['user_name'],
        ];

        app('galog')->log(json_encode($auth_data), 'interface_cib', 'cardAuthRes');
        $auth_res = $this->_cibpay->acSingleAuth($auth_data);
        app('galog')->log($auth_res, 'interface_cib', 'cardAuthRep');

        $result = json_decode($auth_res, true);
        var_dump($result);

        /*if(empty($result['auth_status']) || $result['auth_status'] != '1'){

            $this->_formatResult->setError('MCHSUB.CREATE.BANKCARD.ERROR', $result);
            return $this->_signReturn($this->_formatResult->getData());
        }*/

        $bankCardModel = new \App\Models\Bankcard;
        $bankCardModel->mch_no = $data['mch_no'];
        $bankCardModel->mch_accnt_no = $bizContentFormat['mch_accnt_no'];
        $bankCardModel->bank_no = $bizContentFormat['bank_no'];
        $bankCardModel->bank_name = $bizContentFormat['bank_name'];
        $bankCardModel->card_type = $bizContentFormat['card_type'];
        $bankCardModel->card_no = $bizContentFormat['card_no'];
        $bankCardModel->cert_no = $bizContentFormat['cert_no'];
        $bankCardModel->cert_type = $bizContentFormat['card_no'];
        $bankCardModel->card_cvn = $bizContentFormat['cert_type'];
        $bankCardModel->card_expire_date = $bizContentFormat['card_expire_date'];
        $bankCardModel->cardholder_name = $bizContentFormat['user_name'];
        $bankCardModel->cardholder_phone = $bizContentFormat['card_phone'];
        $bankCardModel->status = Bankcard::SUCCESS;
        $bankCardModel->save();

        $this->_formatResult->setSuccess([
            'mch_accnt_no' => $bankCardModel->mch_accnt_no,
            'card_no' => $bizContentFormat['card_no'],
        ]);
        return $this->_signReturn($this->_formatResult->getData());

    }

    //3.3 子商户解绑银行卡
    public function workMchsubunbindbankcard($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans)
    {
        $bankcard = Bankcard::where('mch_no', $data->mch_no)
            ->where('mch_accnt_no',$bizContentFormat['mch_accnt_no'])
            ->where('card_no',$bizContentFormat['card_no'])
            ->first();

        if($bankcard){
            $this->_formatResult->setError('MCHSUB.BINKCARD.INVALID');
            return $this->_signReturn($this->_formatResult->getData());
        }

        $bankcard->status = Bankcard::UNBIND;
        $bankcard->save();

        $this->_formatResult->setSuccess([
            'mch_accnt_no' => $bankcard->mch_accnt_no,
            'card_no'=>$bankcard->card_no,
        ]);
        return $this->_signReturn($this->_formatResult->getData());
    }

    //3.4 商户批量开设子账户
    public function workMchsubbatchcreate($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans)
    {
        if(count($bizContentFormat['mch_accnts']) > MchAccnt::BATCH_ACCNT_MAX){
            $this->_formatResult->setError('MCHSUB.BATCHCREATE.ACCNT.TOMANY');
            return $this->_signReturn($this->_formatResult->getData());
        }

        $batch_mchaccnt_success = [];
        $bacth_mchaccnt_fail = [];
        foreach ($bizContentFormat['mch_accnts'] as $k=>$mchaccnt){

            if(empty($mchaccnt['bank_cards']) || count($mchaccnt['bank_cards']) > MchAccnt::BATCH_CARD_MAX){
                $bacth_mchaccnt_fail[$k] = [
                    'out_mch_accnt_no' => $mchaccnt['out_mch_accnt_no'],
                    'status' => MchAccnt::BACTH_SUCCESS_STATUS,
                    'desc' => 'out_mch_accnt_no为'.$mchaccnt['out_mch_accnt_no'].'下的银行卡数目非法',
                ];
                continue ;
            }

            $mchaccnt = \App\Models\MchAccnt::where('out_mch_accnt_no', $bizContentFormat['out_mch_accnt_no'])
                ->where('mch_no', $data['mch_no'])
                ->first();
            if($mchaccnt){
                $bacth_mchaccnt_fail[$k] = [
                    'out_mch_accnt_no' => $mchaccnt['out_mch_accnt_no'],
                    'status' => MchAccnt::BACTH_SUCCESS_STATUS,
                    'desc' => 'out_mch_accnt_no为'.$mchaccnt['out_mch_accnt_no'].'已存在',
                ];
                continue ;
            }

            $out_contioue_flag = false;

            foreach ($mchaccnt['bank_cards'] as $bank_card){
                $bank_card_format = array_merge([
                    'bank_no' => '',
                    'bank_name' => '',
                    'card_type' => '',
                    'card_no' => '',
                    'card_cvn' => '',
                    'card_expire_date' => '',
                    'user_name' => '',
                    'card_phone' => '',
                    'cert_type'=>'',
                    'cert_no' => '',
                ],$bank_card);

                $bank_card_existed = \App\Models\Bankcard::where('mch_no', $data['mch_no'])
                    ->where('card_no', $bank_card_format['card_no'])
                    ->first();

                if($bank_card_existed){
                    $bacth_mchaccnt_fail[$k] = [
                        'out_mch_accnt_no' => $mchaccnt['out_mch_accnt_no'],
                        'status' => MchAccnt::BACTH_SUCCESS_STATUS,
                        'desc' => 'out_mch_accnt_no为'.$mchaccnt['out_mch_accnt_no'].';的卡号{'.$bank_card['card_no'].'}已存在',
                    ];
                    $out_contioue_flag = true;
                    break ;
                }

                $auth_data = [
                    'trac_no' => uniqid(),
                    'card_no' => $bank_card['card_no']??'',
                    'bank_no' => $bank_card['bank_no']??'',
                    'acct_type' => $bank_card['acct_type']??'',
                    'cert_type' => $bank_card['cert_type']??'0',
                    'cert_no' => $bank_card['cert_no']??'',
                    'card_phone' => $bank_card['card_phone']??'',
                    'expireDate' => $bank_card['card_expire_date']??'',
                    'cvn' => $bank_card['card_cvn']??'',
                    'user_name' => $bank_card['user_name']??'',
                ];

                app('galog')->log(json_encode($auth_data), 'interface_cib', 'cardAuthRes');
                $auth_res = $this->_cibpay->acSingleAuth($auth_data);
                app('galog')->log($auth_res, 'interface_cib', 'cardAuthRep');

                $result = json_decode($auth_res,true);
                if(empty($result['auth_status']) && $result['auth_status'] != '1'){
                    $bacth_mchaccnt_fail[$k] = [
                        'out_mch_accnt_no' => $mchaccnt['out_mch_accnt_no'],
                        'status' => MchAccnt::BACTH_SUCCESS_STATUS,
                        'desc' => 'out_mch_accnt_no为'.$mchaccnt['out_mch_accnt_no'].'的卡号{'.$bank_card['card_no'].'}认证失败；reson:'.$result['errmsg']??'未知',
                    ];
                    $out_contioue_flag = true;
                    break ;
                }

            }

            if($out_contioue_flag){
                continue ;
            }else{
                $mch_accnt_no = \App\Models\MchAccnt::generateMchAccntNo();
                $mchaccnt_model = new \App\Models\MchAccnt;
                $mchaccnt_model->mch_no = $data['mch_no'];
                $mchaccnt_model->mch_accnt_no = $mch_accnt_no;
                $mchaccnt_model->out_mch_accnt_no = $mchaccnt['out_mch_accnt_no'];
                $mchaccnt_model->mch_accnt_name = $mchaccnt['mch_accnt_name']??'';
                $mchaccnt_model->link_name = $mchaccnt['link_name']??'';
                $mchaccnt_model->link_phone = $mchaccnt['link_phone']??'';
                $mchaccnt_model->link_email = $mchaccnt['link_email']??'';
                $mchaccnt_model->save();
                $batch_mchaccnt_success[$k]['mch_accnts'] = [
                    'out_mch_accnt_no' => $mchaccnt['out_mch_accnt_no'],
                    'mch_accnt_no' => $mch_accnt_no,
                    'status' => MchAccnt::BACTH_SUCCESS_STATUS,
                ];
                foreach ($mchaccnt['bank_cards'] as $bank_card){
                    $bankCardModel = new \App\Models\Bankcard;
                    $bankCardModel->mch_no = $data['mch_no'];
                    $bankCardModel->mch_accnt_no = $mch_accnt_no;
                    $bankCardModel->bank_no = $bank_card['bank_no'];
                    $bankCardModel->bank_name = $bank_card['bank_name'];
                    $bankCardModel->card_type = $bank_card['card_type'];
                    $bankCardModel->card_no = $bank_card['card_no'];
                    $bankCardModel->cert_no = $bank_card['cert_no'];
                    $bankCardModel->cert_type = $bank_card['cert_type'];
                    $bankCardModel->card_cvn = $bank_card['card_cvn']??'';
                    $bankCardModel->card_expire_date = $bank_card['card_expire_date']??'';
                    $bankCardModel->cardholder_name = $bank_card['user_name'];
                    $bankCardModel->cardholder_phone = $bank_card['card_phone'];
                    $bankCardModel->status = Bankcard::SUCCESS;
                    $bankCardModel->save();
                    $batch_mchaccnt_success[$k]['mch_accnts']['bank_cards'][] = $bankCardModel->card_no;
                }
            }

        }

        if(empty($batch_mchaccnt_success)){
            $this->_formatResult->setError([
                'mch_accnts_fail' => $bacth_mchaccnt_fail,
            ]);
            return $this->_signReturn($this->_formatResult->getData());
        }

        $this->_formatResult->setSuccess([
            'mch_accnts_fail' => $bacth_mchaccnt_fail,
            'mch_accnts_success' => $batch_mchaccnt_success,

        ]);
        return $this->_signReturn($this->_formatResult->getData());
    }

    //3.5 子商户查询
    public function workMchsubquery($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans){
        $mchacct = MchAccnt::where('mch_no', $data['mch_no'])
            ->where('mch_accnt_no',$bizContentFormat['mch_accnt_no'])
            ->with('bankCard')->first();

        if(!$mchacct){
            $this->_formatResult->setError('MCHSUB.MCHACCNTNO.INVALID');
            return $this->_signReturn($this->_formatResult->getData());
        }

        $this->_formatResult->setSuccess([
            'mch_accnts' => $mchacct->toArray(),
        ]);
        return $this->_signReturn($this->_formatResult->getData());
    }

    //3.6 商户分账
    public function workMchaccntdispatch($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans){
            foreach($bizContentFormat['split_accnt_detail'] as $k => $split_accnt_detail){
                $bizContentFormat['split_accnt_detail'][$k] = array_merge([
                    'mch_accnt_no' => '',
                    'card_no' => '',
                    'order_no'=>'',
                    'dispatch_event' => '',
                    'amount' => '',
                    'dispatch_type'=>''
                ], $split_accnt_detail);
            }

            $split_accnt_detail_return = [];
            $split_accnt_fail_detail_return = [];

            foreach($bizContentFormat['split_accnt_detail'] as $k => $split_accnt_detail){
                $mchAccnt = MchAccnt::where('mch_accnt_no', $split_accnt_detail['mch_accnt_no'])->first();

                $bank_card = Bankcard::where('card_no',$split_accnt_detail['card_no'])
                    ->where('mch_accnt_no',$split_accnt_detail['mch_accnt_no'])
                    ->where('status',Bankcard::SUCCESS)
                    ->first();

                if(empty($mchAccnt) || empty($bank_card)){
                    $split_accnt_fail_detail_return[$k] = [
                        'mch_accnt_no'=> $split_accnt_detail['mch_accnt_no'],
                        'card_no'=>$split_accnt_detail['card_no'],
                        'status' => MchAccnt::BACTH_FAIL_STATUS,
                        'desc' => 'mch_accnt_no或者card_no非法',
                    ];
                    continue ;
                }

                //代付
                $pay_data = [
                    'order_no' => $split_accnt_detail['order_no'],
                    'to_bank_no' => $bank_card->bank_no,
                    'to_acct_no' => $bank_card->card_no,
                    'to_acct_name' => $bank_card->cardholder_name,
                    'acct_type' => $bank_card->card_type,
                    'trans_amt' => round($split_accnt_detail['amount']/100,2),
                    'trans_usage' => 'test.分账',
                ];

                $pay_res = $this->_cibpay->pyPay($pay_data);
                $pay_result    = json_decode($pay_res,true);
                app('galog')->log($pay_res, 'interface_cib', 'epayReturn');

                var_dump($pay_result);
                if(empty($result['transStatus']) && $result['transStatus'] != '1'){
                    $hisAccntModel = $mchAccnt->createHisAccntModel();
                    $hisAccntModel->transaction_no = $depoTrans->transaction_no;
                    $hisAccntModel->event = $split_accnt_detail['dispatch_event'];
                    $hisAccntModel->event_amt = $split_accnt_detail['amount'];
                    $hisAccntModel->accnt_amt_after = $hisAccntModel->accnt_amt_before + $hisAccntModel->event_amt;
                    $hisAccntModel->save();

                    $mchAccnt->remain_amt = $hisAccntModel->accnt_amt_after;
                    $mchAccnt->save();
                    $split_accnt_detail_return[$k] = [
                        'status' => MchAccnt::BACTH_SUCCESS_STATUS,
                        'mch_accnt_no' => $mchAccnt->mch_accnt_no,
                        'dispatch_event' => $hisAccntModel->event,
                        'amount' => $hisAccntModel->event_amt,
                        'amount_after_event' => $hisAccntModel->accnt_amt_after,
                        'pay_result' => $pay_result
                    ];
                }else{
                    $split_accnt_fail_detail_return[$k] = [
                        'mch_accnt_no'=> $split_accnt_detail['mch_accnt_no'],
                        'card_no'=>$split_accnt_detail['card_no'],
                        'status' => MchAccnt::BACTH_FAIL_STATUS,
                        'desc' => '转账失败',
                    ];
                    continue ;
                }



            }

            $this->_formatResult->setSuccess([
                'split_accnt_success_detail' => $split_accnt_detail_return,
                'split_accnt_fail_detail' => $split_accnt_fail_detail_return,
            ]);
            return $this->_signReturn($this->_formatResult->getData());
        }
}
