<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Gateways\Alipay;

use Runner\NezhaCashier\Requests\Charge;

class Wap extends AbstractAlipayGateway
{
    protected function getChargeMethod(): string
    {
        return 'alipay.trade.wap.pay';
    }

    protected function prepareCharge(Charge $form): array
    {
        return [
            'product_code' => 'QUICK_WAP_WAY',
        ];
    }
}
