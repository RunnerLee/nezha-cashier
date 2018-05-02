<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Gateways\Alipay;

class Qr extends AbstractAlipayGateway
{
    protected function getChargeMethod(): string
    {
        return 'alipay.trade.precreate';
    }

    protected function doCharge(array $payload): array
    {
        $response = $this->request($payload);

        return [
            'charge_url' => $response['qr_code'],
        ];
    }
}
