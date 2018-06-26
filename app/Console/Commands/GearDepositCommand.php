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
    protected $signature = 'command:gear:deposit';
    protected $description = 'Gearman Working: Deposit around functions.';
    
    public function __construct()
    {
        parent::__construct();
        
        $this->beforeRun();
    }
    
    public function handle()
    {
        parent::handle();
        
        //3.1 商户开设子账户
        $this->addWorkerFunction('deposit.mchsub.create', function($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans){

            if(empty($bizContentFormat['out_mch_accnt_no'])){
                $this->_formatResult->setError('OUTMCHACCNTNO.INVALID');
                return $this->_signReturn($this->_formatResult->getData());
            }

            $mchaccnt = \App\Models\MchAccnt::where('out_mch_accnt_no', $bizContentFormat['out_mch_accnt_no'])
                                            ->where('mch_no', $data['mch_no'])
                                            ->first();
            if($mchaccnt){
                $this->_formatResult->setError('OUTMCHACCNTNO.REPEAT');
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
        }, [
            'mch_accnt_name' => '',
            'out_mch_accnt_no' => '',
            'link_name' => '',
            'link_phone' => '',
            'link_email' => '',
        ]);
        echo "Command:Gear:Deposit.mchsub.create is registered.\n";
        
        //3.2 子商户绑定银行卡-提交资料
        $this->addWorkerFunction('deposit.mchsub.bind.bankcard', function($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans){

            $mchaccnt = \App\Models\MchAccnt::where('mch_no', $data['mch_no'])
                                        ->where('mch_accnt_no', $bizContentFormat['mch_accnt_no'])
                                        ->first();

            if(empty($mchaccnt)){
                $this->_formatResult->setError('MCHACCNTNO.NOTFOUND');
                return $this->_signReturn($this->_formatResult->getData());
            }

            $bizContentFormat['card_type'] = in_array($bizContentFormat['card_type'], \App\Models\Bankcard::CARD_TYPE)? $bizContentFormat['card_type'] : '0';
            $bizContentFormat['card_expire_date'] = date('Y-m-d', strtotime($bizContentFormat['card_expire_date']));

            //TODO call cib_interface
            $bank_card_existed = \App\Models\Bankcard::where('mch_no', $data['mch_no'])
                ->where('mch_accnt_no', $bizContentFormat['mch_accnt_no'])
                ->where('card_no', $bizContentFormat['card_no'])
                ->first();

            if($bank_card_existed){
                $this->_formatResult->setError('BANKCARD.REPEAT');
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

            $result = json_decode($auth_res,true);
            var_dump($result);

            /*if(empty($result['auth_status']) || $result['auth_status'] != '1'){

                $this->_formatResult->setError('BANKCARD.AUTH.FAIL', $result);
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
            $bankCardModel->cert_type = $bizContentFormat['cert_type'];
            $bankCardModel->card_cvn = $bizContentFormat['card_cvn'];
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

        }, [
            'mch_accnt_no' => '',
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
        ]);
        echo "Command:Gear:Deposit.mchsub.bind.bankcard is registered.\n";

        //3.3 子商户解绑银行卡
        $this->addWorkerFunction('deposit.mchsub.unbind.bankcard',function($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans){

            $bankcard = Bankcard::where('mch_no', $data['mch_no'])
                ->where('mch_accnt_no',$bizContentFormat['mch_accnt_no'])
                ->where('card_no',$bizContentFormat['card_no'])
                ->first();

            if(!$bankcard){
                $this->_formatResult->setError('BINKCARD.NOTFOUND');
                return $this->_signReturn($this->_formatResult->getData());
            }

            $bankcard->status = Bankcard::UNBIND;
            $bankcard->save();

            $this->_formatResult->setSuccess([
                'mch_accnt_no' => $bankcard->mch_accnt_no,
                'card_no'=>$bankcard->card_no,
            ]);
            return $this->_signReturn($this->_formatResult->getData());

        }, [
            'mch_accnt_no' => '',
            'card_no' => '',
        ]);
        echo "Command:Gear:Deposit.mchsub.query is registered.\n";

        //3.4 商户批量开设子账户
        $this->addWorkerFunction('deposit.mchsub.batchcreate', function($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans){

            if(count($bizContentFormat['mch_accnts']) > MchAccnt::BATCH_ACCNT_MAX){
                $this->_formatResult->setError('BATCHCREATE.ACCNT.INVALID');
                return $this->_signReturn($this->_formatResult->getData());
            }

            $return_batch_mchaccnt = [];
            $fail = true;

            $out_mch_accnt_nos = collect($bizContentFormat['mch_accnts'])->pluck('out_mch_accnt_no')->toArray();

            if (count($out_mch_accnt_nos) != count(array_unique($out_mch_accnt_nos))) {
                $this->_formatResult->setError('OUTMCHACCNTNO.REPEAT',[
                    'mch_accnts' => $bizContentFormat['mch_accnts'],
                ]);
                return $this->_signReturn($this->_formatResult->getData());
            }


            foreach ($bizContentFormat['mch_accnts'] as $k=>$mchaccnt){
                $mchaccnt_format = array_merge([
                    'mch_accnt_name' => '',
                    'out_mch_accnt_no' => '',
                    'link_name' => '',
                    'link_phone' => '',
                    'link_email' => '',
                    'bank_cards' => [],
                ],$mchaccnt);

                if(empty($mchaccnt_format['out_mch_accnt_no'])){
                    $return_batch_mchaccnt[$k] = [
                        'out_mch_accnt_no' => $mchaccnt_format['out_mch_accnt_no'],
                        'status' => MchAccnt::BACTH_FAIL_STATUS,
                        'desc' => 'out_mch_accnt_no 不能为空',
                        'bank_cards' => $mchaccnt_format['bank_cards'],
                    ];
                    continue ;
                }

                if(empty($mchaccnt['bank_cards']) || count($mchaccnt['bank_cards']) > MchAccnt::BATCH_CARD_MAX){
                    $return_batch_mchaccnt[$k] = [
                        'out_mch_accnt_no' => $mchaccnt_format['out_mch_accnt_no'],
                        'status' => MchAccnt::BACTH_FAIL_STATUS,
                        'desc' => 'out_mch_accnt_no为{'.$mchaccnt_format['out_mch_accnt_no'].'}下的银行卡数目非法',
                        'bank_cards' => $mchaccnt_format['bank_cards'],
                    ];
                    continue ;
                }

                $card_nos = collect($mchaccnt_format['bank_cards'])->pluck('card_no')->toArray();

                if (count($card_nos) != count(array_unique($card_nos))) {
                    $return_batch_mchaccnt[$k] = [
                        'out_mch_accnt_no' => $mchaccnt_format['out_mch_accnt_no'],
                        'status' => MchAccnt::BACTH_FAIL_STATUS,
                        'desc' => 'out_mch_accnt_no为{'.$mchaccnt_format['out_mch_accnt_no'].'}中卡号重复',
                        'bank_cards' => $mchaccnt_format['bank_cards'],
                    ];
                    continue ;
                }

                $mchaccnt = \App\Models\MchAccnt::where('out_mch_accnt_no', $mchaccnt_format['out_mch_accnt_no'])
                    ->where('mch_no', $data['mch_no'])
                    ->first();
                if($mchaccnt){
                    $return_batch_mchaccnt[$k] = [
                        'out_mch_accnt_no' => $mchaccnt_format['out_mch_accnt_no'],
                        'status' => MchAccnt::BACTH_FAIL_STATUS,
                        'desc' => 'out_mch_accnt_no为{'.$mchaccnt_format['out_mch_accnt_no'].'}已存在',
                        'bank_cards' => $mchaccnt_format['bank_cards'],
                    ];
                    continue ;
                }

                $out_contioue_flag = false;
                foreach ($mchaccnt_format['bank_cards'] as $bank_card){
                    $bank_card_format = array_merge([
                        'bank_no' => '',
                        'bank_name' => '',
                        'card_type' => '',
                        'card_no' => '',
                        'card_cvn' => '',
                        'card_expire_date' => null,
                        'user_name' => '',
                        'card_phone' => '',
                        'cert_type'=>'',
                        'cert_no' => '',
                    ],$bank_card);

                    if(empty($bank_card_format['card_no'])){
                        $return_batch_mchaccnt[$k] = [
                            'out_mch_accnt_no' => $mchaccnt_format['out_mch_accnt_no'],
                            'status' => MchAccnt::BACTH_FAIL_STATUS,
                            'desc' => 'out_mch_accnt_no为{'.$mchaccnt_format['out_mch_accnt_no'].'}下的card_no不能为空',
                            'bank_cards' => $mchaccnt_format['bank_cards'],
                        ];
                        $out_contioue_flag = true;
                        break ;
                    }

                    $bank_card_existed = \App\Models\Bankcard::where('mch_no', $data['mch_no'])
                        ->where('card_no', $bank_card_format['card_no'])
                        ->first();

                    if($bank_card_existed){
                        $return_batch_mchaccnt[$k] = [
                            'out_mch_accnt_no' => $mchaccnt_format['out_mch_accnt_no'],
                            'status' => MchAccnt::BACTH_FAIL_STATUS,
                            'desc' => 'out_mch_accnt_no为{'.$mchaccnt_format['out_mch_accnt_no'].'}的卡号{'.$bank_card['card_no'].'}已存在',
                            'bank_cards' => $mchaccnt_format['bank_cards'],
                        ];
                        $out_contioue_flag = true;
                        break ;
                    }

                    $auth_data = [
                        'trac_no' => uniqid(),
                        'card_no' => $bank_card_format['card_no'],
                        'bank_no' => $bank_card_format['bank_no'],
                        'acct_type' => $bank_card_format['card_type'],
                        'cert_type' => $bank_card_format['cert_type']??'0',
                        'cert_no' => $bank_card_format['cert_no'],
                        'card_phone' => $bank_card_format['card_phone'],
                        'expireDate' => $bank_card_format['card_expire_date'],
                        'cvn' => $bank_card_format['card_cvn'],
                        'user_name' => $bank_card_format['user_name'],
                    ];

                    app('galog')->log(json_encode($auth_data), 'interface_cib', 'cardAuthRes');
                    $auth_res = $this->_cibpay->acSingleAuth($auth_data);
                    app('galog')->log($auth_res, 'interface_cib', 'cardAuthRep');

                    $result = json_decode($auth_res,true);

                    if(!isset($result['auth_status']) || $result['auth_status'] != '1'){
                        $return_batch_mchaccnt[$k] = [
                            'out_mch_accnt_no' => $mchaccnt_format['out_mch_accnt_no'],
                            'status' => MchAccnt::BACTH_FAIL_STATUS,
                            'desc' => 'out_mch_accnt_no为{'.$mchaccnt_format['out_mch_accnt_no'].'}的卡号{'.$bank_card['card_no'].'}认证失败；reson:'.$result['errmsg']??'未知',
                            'bank_cards' => $mchaccnt_format['bank_cards'],
                        ];
                        $out_contioue_flag = true;
                        break ;
                    }

                }

                if($out_contioue_flag){
                    continue ;
                }else{
                    $fail = false;
                    $mch_accnt_no = \App\Models\MchAccnt::generateMchAccntNo();
                    $mchaccnt_model = new \App\Models\MchAccnt;
                    $mchaccnt_model->mch_no = $data['mch_no'];
                    $mchaccnt_model->mch_accnt_no = $mch_accnt_no;
                    $mchaccnt_model->out_mch_accnt_no = $mchaccnt_format['out_mch_accnt_no'];
                    $mchaccnt_model->mch_accnt_name = $mchaccnt_format['mch_accnt_name'];
                    $mchaccnt_model->accnt_type= MchAccnt::ACCNT_TYPE_MCHSUB;
                    $mchaccnt_model->link_name = $mchaccnt_format['link_name'];
                    $mchaccnt_model->link_phone = $mchaccnt_format['link_phone'];
                    $mchaccnt_model->link_email = $mchaccnt_format['link_email'];
                    $mchaccnt_model->save();
                    $return_batch_mchaccnt[$k] = [
                        'out_mch_accnt_no' => $mchaccnt_format['out_mch_accnt_no'],
                        'mch_accnt_no' => $mch_accnt_no,
                        'status' => MchAccnt::BACTH_SUCCESS_STATUS,
                    ];
                    foreach ($mchaccnt_format['bank_cards'] as $bank_card){
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

                        $bankCardModel = new \App\Models\Bankcard;
                        $bankCardModel->mch_no = $data['mch_no'];
                        $bankCardModel->mch_accnt_no = $mch_accnt_no;
                        $bankCardModel->bank_no = $bank_card_format['bank_no'];
                        $bankCardModel->bank_name = $bank_card_format['bank_name'];
                        $bankCardModel->card_type = $bank_card_format['card_type'];
                        $bankCardModel->card_no = $bank_card_format['card_no'];
                        $bankCardModel->cert_no = $bank_card_format['cert_no'];
                        $bankCardModel->cert_type = $bank_card_format['cert_type'];
                        $bankCardModel->card_cvn = $bank_card_format['card_cvn'];
                        $bankCardModel->card_expire_date = $bank_card_format['card_expire_date'];
                        $bankCardModel->cardholder_name = $bank_card_format['user_name'];
                        $bankCardModel->cardholder_phone = $bank_card_format['card_phone'];
                        $bankCardModel->status = Bankcard::SUCCESS;
                        $bankCardModel->save();
                        $return_batch_mchaccnt[$k]['bank_cards'][]['card_no'] = $bankCardModel->card_no;
                    }
                }
            }

            if($fail){
                $this->_formatResult->setError('MCHSUB.BATCHCREATE.FAIL',[
                    'mch_accnts' => $return_batch_mchaccnt,
                ]);
                return $this->_signReturn($this->_formatResult->getData());
            }

            $this->_formatResult->setSuccess([
                'mch_accnts' => $return_batch_mchaccnt,

            ]);
            return $this->_signReturn($this->_formatResult->getData());

        }, [
            'mch_accnts' => '',
        ]);
        echo "Command:Gear:Deposit.mchsub.create is registered.\n";

        //3.5 子商户查询
        $this->addWorkerFunction('deposit.mchsub.query',function($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans){

            $mchacct = MchAccnt::where('mch_no', $data['mch_no'])
                                ->where('mch_accnt_no',$bizContentFormat['mch_accnt_no'])
                                ->with('bankCard')->first();

            if(!$mchacct){
                $this->_formatResult->setError('MCHACCNTNO.NOTFOUND');
                return $this->_signReturn($this->_formatResult->getData());
            }

            $this->_formatResult->setSuccess([
                'mch_accnts' => $mchacct->toArray(),
            ]);
            return $this->_signReturn($this->_formatResult->getData());
        }, [
            'mch_accnt_no' => '',
        ]);
        echo "Command:Gear:Deposit.mchsub.query is registered.\n";

        //3.6 商户分账
        $this->addWorkerFunction('deposit.mchaccnt.dispatch',function($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans){

            if(count($bizContentFormat['split_accnt_detail']) > MchAccnt::DISPATCH_MAX || empty($bizContentFormat['split_accnt_detail'])){
                $this->_formatResult->setError('ACCNT.INVALID');
                return $this->_signReturn($this->_formatResult->getData());
            }

            foreach($bizContentFormat['split_accnt_detail'] as $k => $split_accnt_detail){
                $bizContentFormat['split_accnt_detail'][$k] = array_merge([
                    'mch_accnt_no' => '',
                    'card_no' => '',
                    'order_no'=>'',
                    'dispatch_event' => '',
                    'dispatch_type'=>'',
                    'amount' => '',
                ], $split_accnt_detail);
            }

            $split_accnt_detail_return = [];

            foreach($bizContentFormat['split_accnt_detail'] as $k => $split_accnt_detail){

                if(!in_array($split_accnt_detail['dispatch_event'], MchAccnt::EVENTS)){
                    $split_accnt_detail_return[$k] = array_merge($split_accnt_detail, [
                        'status' => MchAccnt::DISPATCH_FAIL_STATUS,
                        'desc' => 'dispatch_event非法',
                    ]);
                }

                if(!in_array($split_accnt_detail['dispatch_type'], MchAccnt::DISPATCH_TYPE)){
                    $split_accnt_detail_return[$k] = array_merge($split_accnt_detail, [
                        'status' => MchAccnt::DISPATCH_FAIL_STATUS,
                        'desc' => 'dispatch_type非法',
                    ]);
                }
                $mchAccnt = MchAccnt::where('mch_accnt_no', $split_accnt_detail['mch_accnt_no'])
                                    ->where('mch_no',$data['mch_no'])
                                    ->first();

                $bank_card = Bankcard::where('card_no',$split_accnt_detail['card_no'])
                                    ->where('mch_accnt_no',$split_accnt_detail['mch_accnt_no'])
                                    ->where('status',Bankcard::SUCCESS)
                                    ->first();

                if(empty($mchAccnt) || empty($bank_card)){
                    $split_accnt_detail_return[$k] = array_merge($split_accnt_detail, [
                        'status' => MchAccnt::DISPATCH_FAIL_STATUS,
                        'desc' => 'mch_accnt_no或者card_no不存在',
                    ]);
                    continue ;
                }

                $hisAccntModel = $mchAccnt->createHisAccntModel();
                $hisAccntModel->transaction_no = $depoTrans->transaction_no;
                $hisAccntModel->event = $split_accnt_detail['dispatch_event'];
                $hisAccntModel->event_amt = $split_accnt_detail['amount'];
                $hisAccntModel->accnt_amt_after = $hisAccntModel->accnt_amt_before + $hisAccntModel->event_amt;
                $hisAccntModel->save();

                $mchAccnt->remain_amt = $hisAccntModel->accnt_amt_after;
                $mchAccnt->save();
                $split_accnt_detail_return[$k] = [
                    'mch_accnt_no' => $mchAccnt->mch_accnt_no,
                    'status' => MchAccnt::DISPATCH_SUCCESS_STATUS,
                    'desc' => '',
                    'order_no' => '',
                    'dispatch_event' => $hisAccntModel->event,
                    'dispatch_type' => $hisAccntModel->event_type,
                    'amount' => $hisAccntModel->event_amt,
                    'amount_after_event' => $hisAccntModel->accnt_amt_after,
                ];
            }

            $this->_formatResult->setSuccess([
                'split_accnt_detail' => $split_accnt_detail_return,
            ]);
            return $this->_signReturn($this->_formatResult->getData());
        }, [
            'split_accnt_detail' => [],
        ]);
        echo "Command:Gear:Deposit.mchaccnt.dispatch is registered.\n";

        //3.7 账户提现
        $this->addWorkerFunction('deposit.mchaccnt.withdraw',function($dataOri, $sign, $data, $bizContent, $bizContentFormat, $depoTrans){

            $mchAccnt = MchAccnt::where('mch_accnt_no', $bizContentFormat['mch_accnt_no'])
                ->where('mch_no',$data['mch_no'])
                ->first();
            if(empty($bank_card)){
                $this->_formatResult->setError('MCHACCNTNO.NOTFOUND');
                return $this->_signReturn($this->_formatResult->getData());
            }

            $bank_card = Bankcard::where('card_no',$bizContentFormat['card_no'])
                ->where('mch_accnt_no',$bizContentFormat['mch_accnt_no'])
                ->where('status',Bankcard::SUCCESS)
                ->first();
            if(empty($bank_card)){
                $this->_formatResult->setError('BINKCARD.NOTFOUND');
                return $this->_signReturn($this->_formatResult->getData());
            }

            $req_data = [
                'order_no' => create_uuid(),
                'to_bank_no' => $bank_card->bank_no,
                'to_acct_no' => $bank_card->card_no,
                'to_acct_name' => $bank_card->cardholder_name,
                'acct_type' => $bank_card->card_type,
                'trans_amt' => round($bizContentFormat['amount'] / 100,2),
                'trans_usage' => '',
            ];

            app('galog')->log(json_encode($req_data), 'interface_cib', 'pypayRequest');
            $pay_result    = $this->_cibpay->pyPay($req_data);
            $res_arr = json_decode($pay_result,true);
            app('galog')->log($pay_result, 'interface_cib', 'cardAuthResponse');

            if(isset($res_arr['transStatus']) && $res_arr['transStatus']==1 ){

                $hisAccntModel = $mchAccnt->createHisAccntModel();
                $hisAccntModel->transaction_no = $depoTrans->transaction_no;
                $hisAccntModel->event = MchAccnt::EVENT_WITHDRAW;
                $hisAccntModel->event_amt = $bizContentFormat['amount'];
                $hisAccntModel->accnt_amt_after = $hisAccntModel->accnt_amt_before - $hisAccntModel->event_amt;
                $hisAccntModel->save();

                $mchAccnt->remain_amt = $hisAccntModel->accnt_amt_after;
                $mchAccnt->save();

                $this->_formatResult->setSuccess([
                    'split_accnt_detail' => [
                        'mch_accnt_no' => $mchAccnt->mch_accnt_no,
                        'out_mch_accnt_no' => $mchAccnt->out_mch_accnt_no,
                        'amount' => $bizContentFormat['amount'],
                        'accnt_amt_after' => $hisAccntModel->accnt_amt_after,
                    ],
                ]);
            }


            return $this->_signReturn($this->_formatResult->getData());
        }, [
            'mch_accnt_no' => '' ,
            'card_no' => '' ,
            'bsbank' => '' ,
            'amount' => '' ,
        ]);
        echo "Command:Gear:Deposit.mchaccnt.withdraw is registered.\n";

        echo "Command:Gear:Deposit Is Launched Successfully\n";
        while ($this->_worker->work());
    }
}
