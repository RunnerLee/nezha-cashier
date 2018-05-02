<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Gateways\Union;

use Runner\NezhaCashier\Requests\Charge;
use Runner\NezhaCashier\Requests\Query;

class Web extends AbstractUnionGateway
{
    /**
     * 支付.
     *
     * @param Charge $form
     *
     * @return array
     */
    public function charge(Charge $form): array
    {
        $payload = $this->createPayload(
            array_merge(
                [
                    'txnTime' => date('YmdHis', $form->get('created_at')),
                    'txnType' => '01',
                    'txnSubType' => '01',
                    'frontUrl' => $form->get('return_url'),
                    'backUrl' => $this->config->get('notify_url'),
                    'channelType' => '08',
                    'orderId' => $form->get('order_id'),
                    'txnAmt' => (int) ($form->get('amount') * 1000 / 10),
                    'currencyCode' => '156',
                    'defaultPayType' => '0001',
                ],
                $form->get('extras')
            )
        );

        return [
            'charge_url' => self::WEB_CHARGE,
            'parameters' => $payload,
        ];
    }

    /**
     * 查询.
     *
     * @param Query $form
     *
     * @return array
     */
    public function query(Query $form): array
    {
        $payload = $this->createPayload(
            array_merge(
                [
                    'txnTime' => date('YmdHis', $form->get('created_at')),
                    'txnType' => '00',
                    'txnSubType' => '00',
                    'orderId' => $form->get('order_id'),
                ],
                $form->get('extras')
            )
        );

        $response = $this->request(self::WEB_QUERY_ORDER, $payload);

        return [
            'order_id' => $response['orderId'],
            'status' => $this->formatTradeStatus($response['origRespCode']),
            'trade_sn' => $response['queryId'] ?? '',
            'buyer_identifiable_id' => '',
            'amount' => ($response['settleAmt'] ?? 0) / 100,
            'buyer_name' => '',
            'raw' => $response,
        ];
    }
}
