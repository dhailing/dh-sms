<?php
/**
 * 普通短信
 * Created by Dh106
 * User: DH
 * Email: 206989662@qq.com
 * Date: 2019/8/30
 * Time: 11:06
 */

namespace dhsms\Send\Gateways\Tencent;


class SingleGateway extends Tencent
{
    public function send(array $config_biz)
    {
        $config_biz['url'] = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms';
        $config_biz['gateway'] = 'Single';
        parent::send($config_biz); // TODO: Change the autogenerated stub
        return $this->doSend();
    }
}
