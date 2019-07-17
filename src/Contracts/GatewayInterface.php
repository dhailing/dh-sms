<?php

namespace ninenight\Send\Contracts;

interface GatewayInterface
{
    /**
     * pay a order.
     *
     * @author yansongda <me@206989662@qq.com>
     *
     * @param array $config_biz
     *
     * @return mixed
     */
    public function send(array $config_biz);


}
