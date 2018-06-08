<?php

return [
    'current_key' => 'yinzhun',  
    
    'test' => [
        // 商户号，格式如A0000001
        'appid'		=> 'Q0000279',
        
        // 商户私有密钥，该密钥需要严格保密，只能出现在后台服务端代码中，不能放在可能被用户查看到的地方，如html、js代码等
        // 在发送报文给收付直通车时，会使用该密钥进行签名（SHA1算法方式）
        // 在收到收付直通车返回的报文时，将使用该密钥进行验签
        'commKey'		=> "D6893DBCB1544B0AAAC73C562C06F1EC",
        
        // 商户客户端证书路径，该证书需要严格保密
        // 在发送报文给收付直通车时，会使用该密钥进行签名（RSA算法方式）
        'mrch_cert'		=> dirname(__FILE__).'/key/appsvr_client.pfx',
        // 以下证书参数一般为默认值，无需更改
        'mrch_cert_pwd'	=> '123456',
        // 收付直通车服务器证书，RSA算法验签使用
        'cib_cert_test'	=> dirname(__FILE__).'/key/appsvr_server_test.pem',
        'cib_cert_prod'	=> dirname(__FILE__).'/key/appsvr_server_prod.pem',
        
        // 二级商户名称，可为空
        'sub_mrch'	=> "SDK-PHP测试商城",
        
        // 是否为开发测试模式，true时将连接测试环境，false时将连接生产环境
        'isDevEnv'	=> true,
        // 是否验签，true验证应答报文签名，false不验证签名，开发调试时可修改此项为false，生产环境请更改为true
        'needChkSign' => true,
        
        // 代理设置，设为null为不使用代理
        'proxy_ip'	=> null,
        'proxy_port'	=> null,
        //'proxy_ip'	=> '1.2.3.4',
        //'proxy_port'	=> 8080,
    ], 
    
    'yinzhun' => [
        // 商户号，格式如A0000001
        'appid'		=> 'Q0001469',
        
        // 商户私有密钥，该密钥需要严格保密，只能出现在后台服务端代码中，不能放在可能被用户查看到的地方，如html、js代码等
        // 在发送报文给收付直通车时，会使用该密钥进行签名（SHA1算法方式）
        // 在收到收付直通车返回的报文时，将使用该密钥进行验签
        'commKey'		=> "D6893DBCB1544B0AAAC73C562C06F1EC",
        
        // 商户客户端证书路径，该证书需要严格保密
        // 在发送报文给收付直通车时，会使用该密钥进行签名（RSA算法方式）
        'mrch_cert'		=> dirname(__FILE__).'/cibkey/appsvr_client.pfx',
        // 以下证书参数一般为默认值，无需更改
        'mrch_cert_pwd'	=> '123456',
        // 收付直通车服务器证书，RSA算法验签使用
        'cib_cert_test'	=> dirname(__FILE__).'/cibkey/appsvr_server_test.pem',
        'cib_cert_prod'	=> dirname(__FILE__).'/cibkey/appsvr_server_prod.pem',
        
        // 二级商户名称，可为空
        'sub_mrch'	=> "杭州银准网络科技有限公司",
        
        // 是否为开发测试模式，true时将连接测试环境，false时将连接生产环境
        'isDevEnv'	=> true,
        // 是否验签，true验证应答报文签名，false不验证签名，开发调试时可修改此项为false，生产环境请更改为true
        'needChkSign' => false,
        
        // 代理设置，设为null为不使用代理
        'proxy_ip'	=> null,
        'proxy_port'	=> null,
        //'proxy_ip'	=> '1.2.3.4',
        //'proxy_port'	=> 8080,
    ]
];