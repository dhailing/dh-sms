<?php
/**
 * Created by Dh106
 * User: DH
 * Email: 206989662@qq.com
 * Date: 2019/7/17
 * Time: 14:09
 */

namespace ninenight\Send;


use ninenight\Send\Exceptions\InvalidArgumentException;
use ninenight\Send\Support\Config;

class Send
{
    /**
    * @var \Ninenight\Pay\Support\Config
    */
    private $config;

    /**
     * @var string
     */
    private $drivers;

    /**
     * @var \Ninenight\Pay\Contracts\GatewayInterface
     */
    private $gateways;

    /**
     * construct method.
     *
     * @author JasonYan <me@206989662@qq.com>
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = new Config($config);
    }

    /**
     * set pay's driver.
     *
     * @author JasonYan <me@206989662@qq.com>
     *
     * @param string $driver
     *
     * @return Pay
     */
    public function driver($driver)
    {
        if (is_null($this->config->get($driver))) {
            throw new InvalidArgumentException("Driver [$driver]'s Config is not defined.");
        }

        $this->drivers = $driver;

        return $this;
    }

    /**
     * set pay's gateway.
     *
     * @author Ninenight <me@206989662@qq.com>
     *
     * @param string $gateway
     *
     * @return \Ninenight\Pay\Contracts\GatewayInterface
     */
    public function gateway($gateway = 'web')
    {
        if (!isset($this->drivers)) {
            throw new InvalidArgumentException('Driver is not defined.');
        }

        $this->gateways = $this->createGateway($gateway);

        return $this->gateways;
    }

    /**
     * create pay's gateway.
     *
     * @author Ninenight <me@206989662@qq.com>
     *
     * @param string $gateway
     *
     * @return \Ninenight\Pay\Contracts\GatewayInterface
     */
    protected function createGateway($gateway)
    {
        if (!file_exists(__DIR__.'/Gateways/'.ucfirst($this->drivers).'/'.ucfirst($gateway).'Gateway.php')) {
            throw new InvalidArgumentException("Gateway [$gateway] is not supported.");
        }

        $gateway = __NAMESPACE__.'\\Gateways\\'.ucfirst($this->drivers).'\\'.ucfirst($gateway).'Gateway';

        return $this->build($gateway);
    }

    /**
     * build pay's gateway.
     *
     * @author JasonYan <me@206989662@qq.com>
     *
     * @param string $gateway
     *
     * @return \Ninenight\Pay\Contracts\GatewayInterface
     */
    protected function build($gateway)
    {
        return new $gateway($this->config->get($this->drivers));
    }
}
