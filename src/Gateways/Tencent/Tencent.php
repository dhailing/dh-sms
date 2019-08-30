<?php
/**
 * Created by Dh106
 * User: DH
 * Email: 206989662@qq.com
 * Date: 2019/8/30
 * Time: 10:35
 */

namespace dhsms\Send\Gateways\Tencent;


use dhsms\Send\Gateways\Tencent\Util\SmsSenderUtil;
use ninenight\Send\Contracts\GatewayInterface;
use ninenight\Send\Exceptions\InvalidArgumentException;
use ninenight\Send\Support\Config;

abstract class Tencent implements GatewayInterface
{
    /**
     * @var string 请求地址
     * User: DH
     * Email: 206989662@qq.com
     * Date: 2019/8/30
     * Time: 15:14
     */
    protected $gateway;

    /**
     * @var array 默认配置
     * User: DH
     * Email: 206989662@qq.com
     * Date: 2019/8/30
     * Time: 15:15
     */
    protected $config;

    /**
     * @var array 用户配置
     * User: DH
     * Email: 206989662@qq.com
     * Date: 2019/8/30
     * Time: 15:15
     */
    protected $user_config;

    /**
     * @var \stdClass object 请求数据
     * User: DH
     * Email: 206989662@qq.com
     * Date: 2019/8/30
     * Time: 15:15
     */
    protected $data;

    /**
     * @var \stdClass object 请求电话
     * User: DH
     * Email: 206989662@qq.com
     * Date: 2019/8/30
     * Time: 15:19
     */
    protected $tel;

    /**
     * @var int 国际编码 86中国
     * User: DH
     * Email: 206989662@qq.com
     * Date: 2019/8/30
     * Time: 15:19
     */
    protected $nation_code = 86;

    /**
     * @var int 短信类型,0=普通短信,1=营销短信
     * User: DH
     * Email: 206989662@qq.com
     * Date: 2019/8/30
     * Time: 15:19
     */
    protected $type = 0;

    /**
     * 初始化
     * Tencent constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->user_config = new Config($config);
        if (is_null($this->user_config->get('appid'))) {
            throw new InvalidArgumentException('配置信息有误');
        }

        $this->config = [
            'appid' => $this->user_config->get('appid'),    //appid
            'appkey' => $this->user_config->get('appkey'),  //appkey
        ];

        $this->data = new \stdClass();
        $this->tel = new \stdClass();
    }

    /**
     * 发送短信
     * @param array $config_biz
     *      'code' string 验证码
     *      'mobile' string 手机号
     *      'mobile_array'  array 群发时手机号数组
     *      'extend' string 额外信息
     *      'msg' string 短信内容,适合非模板短信
     *      'ext' string 透明数据,如何传过去,如何传回来
     *      'type' int 短信类型:0=普通短信,1=营销短信,默认0
     *      'nation_code' int 手机号国际码, 默认86
     *      'playtimes' int 播放次数,针对语音短信,默认2
     *      'templateid' string 模板id
     *      'params' array 模板短信内容中的参数
     * @return mixed|void
     * User: DH
     * Email: 206989662@qq.com
     * Date: 2019/8/30
     * Time: 15:20
     */
    public function send(array $config_biz)
    {
        // TODO: Implement send() method.
        $util = new SmsSenderUtil();
        $gateway = $config_biz['gateway'];

        $code = $config_biz['code'];
        $curTime = time();
        $this->gateway = $config_biz['url'] . "?sdkappid=" . $this->config['appid'] . "&random=" . $code;

        $nation_code = isset($config_biz['nation_code']) ? $config_biz['nation_code'] : $this->nation_code;
        $this->tel->nationcode = "" . $nation_code;
        $this->tel->mobile = "" . $config_biz['mobile'];

        $this->data->tel = $this->tel;
        $this->data->time = $curTime;
        $this->data->ext = isset($config_biz['ext']) ? $config_biz['ext'] : '';

        switch ($gateway) {
            case 'Single':
                $templetId = isset($config_biz['templateid']) ? $config_biz['templateid'] : '';
                $this->data->extend = isset($config_biz['extend']) ? $config_biz['extend'] : '';
                if ($templetId) {
                    //指定模板短信
                    $this->data->sig = $util->calculateSigForTempl($this->config['appkey'], $code, $curTime, $config_biz['mobile']);
                    $this->data->tpl_id = $templetId;
                    $this->data->params = $config_biz['params'];    //模板中的参数[{1},{2},{3}]
                    $this->data->sign = $this->getSign();
                } else {
                    //普通短信
                    $type = isset($config_biz['type']) ? $config_biz['type'] : $this->type;
                    $this->data->type = (int)$type;
                    $this->data->msg = $config_biz['msg'];
                    $this->data->sig = $this->getShaSign($code, $curTime, $config_biz['mobile']);
                }
                break;
            case 'VoiceVerify':
                $this->data->msg = $config_biz['msg'];
                $this->data->playtimes = isset($config_biz['playtimes']) ? $config_biz['playtimes'] : 2;
                $this->data->sig = $this->getShaSign($code, $curTime, $config_biz['mobile']);
                break;
            case 'VoicePrompt':
                $this->data->msg = $config_biz['msg'];
                $this->data->prompttype = 2;
                $this->data->playtimes = isset($config_biz['playtimes']) ? $config_biz['playtimes'] : 2;
                $this->data->sig = $this->getShaSign($code, $curTime, $config_biz['mobile']);
                break;
            case 'Multi':
                $templetId = isset($config_biz['templateid']) ? $config_biz['templateid'] : '';
                $this->data->tel = $util->phoneNumbersToArray($nation_code, $config_biz['mobile_array']);
                $this->data->extend = isset($config_biz['extend']) ? $config_biz['extend'] : '';
                if ($templetId) {
                    $this->data->sign = $this->getSign();
                    $this->data->tpl_id = $templetId;
                    $this->data->params = $config_biz['params'];    //模板中的参数[{1},{2},{3}]
                    $this->data->sig = $util->calculateSigForTemplAndPhoneNumbers($this->config['appkey'], $code, $curTime, $config_biz['mobile_array']);
                } else {
                    $type = isset($config_biz['type']) ? $config_biz['type'] : $this->type;
                    $this->data->type = (int)$type;
                    $this->data->msg = $config_biz['msg'];
                    $this->data->sig = $util->calculateSig($this->config['appkey'], $code, $curTime, $config_biz['mobile_array']);
                }
                break;
            default:
                break;
        }
    }

    /**
     * 签名,默认签名为空
     * @return string
     * User: DH
     * Email: 206989662@qq.com
     * Date: 2019/8/30
     * Time: 15:28
     */
    private function getSign()
    {
        return '';
    }

    /**
     * hash加密
     * @param $code
     * @param $curTime
     * @param $mobile
     * @return string
     * User: DH
     * Email: 206989662@qq.com
     * Date: 2019/8/30
     * Time: 15:28
     */
    private function getShaSign($code, $curTime, $mobile)
    {
        return hash("sha256", "appkey=" . $this->config['appkey'] . "&random=" . $code . "&time=" . $curTime . "&mobile=" . $mobile, FALSE);
    }

    /**
     * 发送短信
     * @return bool|string
     * User: DH
     * Email: 206989662@qq.com
     * Date: 2019/8/30
     * Time: 15:29
     */
    public function doSend()
    {
        $util = new SmsSenderUtil();

        return $util->sendCurlPost($this->gateway, $this->data);
    }
}
