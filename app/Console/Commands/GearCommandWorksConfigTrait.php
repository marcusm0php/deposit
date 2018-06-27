<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2018/6/27
 * Time: 15:22
 */

namespace App\Console\Commands;


trait GearCommandWorksConfigTrait
{
    protected $_gear_works = [
        'deposit.sign.verify' => [],
        'deposit.outtransno.verify' => [],







        'deposit.mchsub.create' => [
            'mch_accnt_name' => '',
            'out_mch_accnt_no' => '',
            'link_name' => '',
            'link_phone' => '',
            'link_email' => '',
        ],
        'deposit.mchsub.bind.bankcard' => [
            'mch_accnt_no' => '',
            'bank_no' => '',
            'bank_name' => '',
            'card_type' => '',
            'card_no' => '',
            'card_cvn' => '',
            'card_expire_date' => '',
            'user_name' => '',
            'card_phone' => '',
            'cert_type' => '',
            'cert_no' => '',
        ],
        'deposit.mchsub.unbind.bankcard' => [
            'mch_accnt_no' => '',
        ],
        'deposit.mchsub.batchcreate' => [
            'mch_accnts' => '',
        ],
        'deposit.mchsub.query' => [
            'mch_accnt_no' => '',
        ],
        'deposit.mchaccnt.dispatch' => [
            'split_accnt_detail' => [],
        ],
        'deposit.mchaccnt.withdraw' => [
            'mch_accnt_no' => '',
            'card_no' => '',
            'bsbank' => '',
        ],
    ];

}