<?php
/**
 * Created by Dh106
 * User: DH
 * Email: 206989662@qq.com
 * Date: 2019/7/17
 * Time: 13:55
 */

namespace ninenight\Send\Gateways\Xuanwu;


use ninenight\Send\Contracts\GatewayInterface;
use ninenight\Send\Exceptions\InvalidArgumentException;
use ninenight\Send\Support\Config;

abstract class Xuanwu implements GatewayInterface
{
    protected $gateway = 'http://211.147.239.62/Service/WebService.asmx?wsdl';

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
            'subid' => $this->user_config->get('subid'),
            'mobile' => '',
            'content' => '',
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
        $this->config['mobile'] = $config_biz['mobile'];
        $this->config['content'] = $config_biz['content'];
    }

    /**
     * 执行发送
     * @return mixed
     * @throws \SoapFault
     * User: DH
     * Email: 206989662@qq.com
     * Date: 2019/7/17
     * Time: 16:00
     */
    protected function doSend()
    {
        libxml_disable_entity_loader(false);

        $client = new \SoapClient($this->gateway);

        $ret = $client->PostSingle($this->config);
        $result = $ret->PostSingleResult;

        return $result;
    }
}
