<?php
/**
 * Created by Dh106
 * User: DH
 * Email: 206989662@qq.com
 * Date: 2019/7/17
 * Time: 13:55
 */

namespace ninenight\Send\Gateways\Juzhixin;


use ninenight\Send\Contracts\GatewayInterface;
use ninenight\Send\Exceptions\InvalidArgumentException;
use ninenight\Send\Support\Config;

abstract class Juzhixin implements GatewayInterface
{
    protected $gateway = 'http://sms.juzhixin.com.cn/v2sms.aspx?action=send';

    protected $config;

    protected $user_config;

    public function __construct(array $config)
    {
        $this->user_config = new Config($config);

        if(is_null($this->user_config->get('subid'))) {
            throw new InvalidArgumentException('配置信息有误');
        }

        $this->config = [
            'account' => $this->user_config->get('account'),
            'password' => $this->user_config->get('password'),
            'userid' => $this->user_config->get('subid'),
            'timestamp' => date('YmdHis'),
            'sign' => '',
            'mobile' => '',
            'content' => '',
            'sendTime' => '',
        ];
    }

    /**
     * 发送
     * @param array $config_biz
     * @return mixed
     * User: DH
     * Email: 206989662@qq.com
     * Date: 2019/7/17
     * Time: 14:23
     */
    public function send(array $config_biz)
    {
        // TODO: Implement send() method.
        $this->config['sign'] = $this->getsign();
        $this->config['mobile'] = $config_biz['mobile'];
        $this->config['content'] = $config_biz['content'];
        $this->config['sendTime'] = isset($config_biz['sendTime']) && !empty($config_biz['sendTime']) ?: '';
    }

    /**
     * 获取签名
     * @return string
     * User: DH
     * Email: 206989662@qq.com
     * Date: 2019/7/17
     * Time: 14:23
     */
    public function getsign()
    {
        return md5($this->config['account'] . $this->config['password'] . $this->config['timestamp']);
    }

    /**
     * 执行发送
     * @return mixed
     * User: DH
     * Email: 206989662@qq.com
     * Date: 2019/7/17
     * Time: 14:23
     */
    protected function doSend()
    {
        $params = '';
        foreach ($this->config as $key => $val) {
            $params .= "$key=" . urlencode($val) . "&";
        }
        $postData = substr($params, 0, -1);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $this->gateway);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);

        //xml转换
        $xml = simplexml_load_string($data);
        $redata = json_decode(json_encode($xml),true);

        $result = [];
        if($redata['returnstatus'] == 'Success') {
            $result['msg'] = 'success';
            $result['code'] = 200;
        } else {
            $result['msg'] = $redata['message'];
            $result['code'] = 500;
        }

        return $result;
    }
}
