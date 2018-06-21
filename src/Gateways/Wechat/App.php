<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Gateways\Wechat;

use Runner\NezhaCashier\Requests\Charge;

class App extends AbstractWechatGateway
{
    /**
     * @param Charge $form
     *
     * @return array
     */
    protected function prepareCharge(Charge $form): array
    {
        return [];
    }

    protected function doCharge(array $response, Charge $form): array
    {
        $parameters = [
            'appid'     => $this->config->get('app_id'),
            'partnerid' => $this->config->get('mch_id'),
            'prepayid'  => $response['prepay_id'],
            'package'   => 'Sign=WXPay',
            'timestamp' => time(),
            'noncestr'  => uniqid(),
        ];
        $parameters['sign'] = $this->sign($parameters);

        return [
            'charge_url' => '',
            'parameters' => $parameters,
        ];
    }

    protected function getTradeType(): string
    {
        return 'APP';
    }
}
