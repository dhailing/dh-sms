<?php
/**
 * Created by Dh106
 * User: DH
 * Email: 206989662@qq.com
 * Date: 2019/7/17
 * Time: 13:55
 */

namespace ninenight\Send\Gateways\Aliyun;


use ninenight\Send\Contracts\GatewayInterface;
use ninenight\Send\Exceptions\InvalidArgumentException;
use ninenight\Send\Support\Config;

abstract class Aliyun implements GatewayInterface
{
    protected $gateway = 'http://dysmsapi.aliyuncs.com/';

    protected $config;

    protected $user_config;

    public function __construct(array $config)
    {
        $this->user_config = new Config($config);

        if (is_null($this->user_config->get('accessKeyId'))) {
            throw new InvalidArgumentException('配置信息有误');
        }

        $this->config = [
            'AccessKeyId' => $this->user_config->get('accessKeyId'),
            'accessKeySecret' => $this->user_config->get('accessKeySecret'),
            'SignName' => $this->user_config->get('signName'),
            'Timestamp' => gmdate("Y-m-d\TH:i:s\Z"),
            'SignatureVersion' => '1.0',
            'SignatureNonce' => uniqid(mt_rand(0, 0xffff), true),
            'SignatureMethod' => 'HMAC-SHA1',
            'Format' => 'JSON',
            'RegionId' => 'cn-hangzhou',
            'Action' => 'SendSms',
            'Version' => '2017-05-25',
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
        $this->config['PhoneNumbers'] = $config_biz['mobile'];
        $this->config['TemplateCode'] = $config_biz['TemplateCode'];
        $this->config['TemplateParam'] = json_encode($config_biz['templateParam'], JSON_UNESCAPED_UNICODE);
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
        ksort($this->config);
        $sortedQueryStringTmp = "";
        foreach ($this->config as $key => $value) {
            $sortedQueryStringTmp .= "&" . $this->encode($key) . "=" . $this->encode($value);
        }

        $stringToSign = "GET&%2F&" . $this->encode(substr($sortedQueryStringTmp, 1));
        $sign = base64_encode(hash_hmac("sha1", $stringToSign, $this->config['accessKeySecret'] . "&", true));
        $signature = $this->encode($sign);

        $this->gateway .= "?Signature={$signature}{$sortedQueryStringTmp}";
    }

    /**
     * 编码
     * @param $str
     * @return mixed|string
     */
    private function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
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
        $this->getsign();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gateway);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("x-sdk-client" => "php/2.0.0"));
        if (substr($this->gateway, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $result = curl_exec($ch);
        if ($result === false) {
           throw new InvalidArgumentException("[CURL_" . curl_errno($ch) . "]: " . curl_error($ch));
        }
        curl_close($ch);

        return json_decode($result, true);
    }
}
