<?php
/**
 * Created by dhailing
 * User: dhailing
 * Email: test@test.com
 * Date: 2020/12/17 0017
 * Time: 20:37
 */

namespace ninenight\Send\Gateways\FlyingPigeon;


use ninenight\Send\Contracts\GatewayInterface;
use ninenight\Send\Exceptions\InvalidArgumentException;
use ninenight\Send\Support\Config;

abstract class FlyingPigeon implements GatewayInterface
{
    protected $gateway = 'http://api.feige.ee/SmsService/Template';

    protected $config;

    protected $user_config;

    public function __construct(array $config)
    {
        $this->user_config = new Config($config);

        if(is_null($this->user_config->get('account'))) {
            throw new InvalidArgumentException('配置信息有误');
        }

        $this->config = [
            'Account' => $this->user_config->get('account'),
            'Pwd' => $this->user_config->get('password'),
            'SignId' => $this->user_config->get('signId'),
            'Mobile' => '',
            'Content' => '',
            'TemplateId' => '',
        ];
    }


    public function send(array $config_biz)
    {
        $this->config['Mobile'] = $config_biz['mobile'];
        $this->config['Content'] = $config_biz['content'];
        $this->config['TemplateId'] = $config_biz['templateId'];
    }

    public function doSend()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gateway);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); //在HTTP请求中包含一个"User-Agent: "头的字符串。
        curl_setopt($ch, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出。
        curl_setopt($ch, CURLOPT_POST, true); //发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->config);//Post提交的数据包
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //文件流形式
        curl_setopt($ch, CURLOPT_TIMEOUT, 20); //设置cURL允许执行的最长秒数。
        $res = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($res, true);
        $result = [];
        if($res->Code != 0) {
            $result['msg'] = 'fail';
            $result['code'] = 500;
        } else {
            $result['msg'] = 'success';
            $result['code'] = 200;
        }
        return $result;
    }
}
