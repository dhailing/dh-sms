<?php
/**
 * 语音验证码
 * Created by Dh106
 * User: DH
 * Email: 206989662@qq.com
 * Date: 2019/8/30
 * Time: 11:04
 */

namespace dhsms\Send\Gateways\Tencent;


class VoiceVerifyGateway extends Tencent
{
    public function send(array $config_biz)
    {
        $config_biz['url'] = 'https://yun.tim.qq.com/v5/tlsvoicesvr/sendvoice';
        $config_biz['gateway'] = 'VoiceVerify';
        parent::send($config_biz); // TODO: Change the autogenerated stub
        return $this->doSend();
    }
}
