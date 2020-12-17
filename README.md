**ninenight/dh-sms**

`composer require ninenight/dh-sms`

- 集合常用的短信接口,不用每次都复制方法什么的,旨在即装即用

## 聚知信

```$xslt
public static function juzhixin($data)
{
    $user_config = [            //申请信息配置
        'juzhixin' => [         //聚知信配置
            'account' => '',    //账号
            'password' => '',   //密码
            'subid' => ,        //用户id
        ]
    ];

    $code = mt_rand(1000, 9999);
    $config_biz = [
        'sendTime' => '',           //配置定时发送时间,不定时,为空
        'mobile' => '182********',  //手机号
        'content' => '【XXX】您的验证码是' . $code . ',请在5分钟内使用。'   //发送内容
    ];

    $sms = new Send($user_config);  //实例化短信发送对象

    $result = $sms->driver('juzhixin')->gateway('action')->send($config_biz);   //发送短信

    dd($result);    //打印发送结果
    
//    array:5 [
//      "returnstatus" => "Success"
//      "message" => "ok"
//      "remainpoint" => "98481"
//      "taskID" => "116459"
//      "successCounts" => "1"
//    ]
}

```

## 玄武短信

```$xslt
public static function xuanwu()
{
    $user_config = [            //申请信息配置
        'xuanwu' => [           //玄武短信配置
            'account' => '',    //账号
            'password' => '',   //密码
            'subid' => '',      //用户id 可以为空
        ]
    ];

    $code = mt_rand(1000, 9999);
    $config_biz = [
        'mobile' => '182********',
        'content' => '您的验证码是' . $code . ',请在5分钟内使用。'
    ];

    $sms = new Send($user_config);

    $result = $sms->driver('xuanwu')->gateway('action')->send($config_biz);

    dd($result);
    
    //0 表示发送成功
}

```

## 阿里云短信

```$xslt
public static function aliyun()
{
    $user_config = [                    //阿里云申请的配置信息
        'aliyun' => [
            'accessKeyId' => '',
            'accessKeySecret' => '',
            'signName' => '',
        ]
    ];

    $code = mt_rand(1000, 9999);
    $config_biz = [
        'mobile' => '182********',
        'templateParam' => ['code' => $code],   //模板中的变量
        'TemplateCode' => 'SMS_15******'        //阿里云申请的模板号/id
    ];

    $sms = new Send($user_config);

    $result = $sms->driver('aliyun')->gateway('action')->send($config_biz);

    dd($result);
    
//    array:4 [
//      "Message" => "OK"
//      "RequestId" => "27B11BA9-2A2E-4D64-AC7F-CE8CDB4386A2"
//      "BizId" => "824800563362009295^0"
//      "Code" => "OK"
//    ]
}

```

## 飞鸽传书

```$xslt
public static function feige()
{
    $user_config = [                    //飞鸽传书短信的配置信息
        'feige' => [
            'account' => '',
            'password' => '',
            'signId' => '',
        ]
    ];
    $config_biz = [
        'mobile' => '182********',
        'content' => $param1 . '||' . $param2 . '||' . $param3,   //模板中的变量
        'templateId' => '117***'        //申请的模板号/id
    ];

    $sms = new Send($user_config);

    $result = $sms->driver('flyingpigeon')->gateway('action')->send($config_biz);

    dd($result);
    
}
```
