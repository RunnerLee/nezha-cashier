<?php

namespace Runner\NezhaCashier\Gateways\Pingan;

use Runner\NezhaCashier\Exception\GatewayMethodNotSupportException;
use Runner\NezhaCashier\Gateways\AbstractGateway;
use Runner\NezhaCashier\Requests\Charge;
use Runner\NezhaCashier\Requests\Close;
use Runner\NezhaCashier\Requests\Query;
use Runner\NezhaCashier\Requests\Refund;
use Runner\NezhaCashier\Utils\Config;
use Wangjian\PinganPay\Client;
use DateTime;
use DateTimeZone;

abstract class AbstractPinganGateway extends AbstractGateway
{
    /**
     * pingan payment client.
     *
     * @var Client
     */
    protected $client;

    /**
     * AbstractPinganGateway constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);

        $this->client = new Client(
            $this->config->get('openid'),
            $this->config->get('openkey'),
            $this->config->get('test', false)
        );

        if (!is_null($privateKeyPath = $this->config->get('private_key', null))) {
            $this->client->setPrivateKey($privateKeyPath);
        }
    }

    /**
     * @param Charge $form
     *
     * @return array
     */
    public function charge(Charge $form): array
    {
        if ($form->get('currency') != 'CNY') {
            throw new \InvalidArgumentException('only the CNY currenry is supported');
        }

        $options = array_merge([
            'out_no'          => $form->get('order_id'),
            'pmt_tag'         => $this->config->get('pmt_tag'),
            'original_amount' => $form->get('amount'),
            'trade_amount'    => $form->get('amount'),
            'ord_name'        => $form->get('subject'),
            'trade_account'   => $this->config->get('trade_account', null),
            'remark'          => $form->get('description'),
            'notify_url'      => $this->config->get('notify_url'),
            'tag'             => $this->config->get('tag', null),
        ], $this->prepareCharge($form));

        $response = $this->client->charge($options);

        return $this->doCharge($response, $form);
    }

    /**
     * @param Refund $form
     *
     * @return array
     */
    public function refund(Refund $form): array
    {
        $options = [
            'sign_type'     => $this->config->get('sign_type', 'RSA'),
            'out_no'        => $form->get('order_id'),
            'refund_out_no' => $form->get('refund_id'),
            'refund_amount' => $form->get('refund_amount'),
            'shop_pass'     => $this->config->get('shop_pass', null),
            'trade_account' => $this->config->get('trade_account', null),
        ];

        if (!empty($form->get('trade_no'))) {
            $options['trade_no'] = $form->get('trade_no');
        }

        $response = $this->client->refund($options);

        return [
            'refund_sn'     => $response['ord_no'],
            'refund_amount' => $response['trade_amount'],
            'raw'           => $response,
        ];
    }

    /**
     * @param Close $form
     *
     * @return array
     */
    public function close(Close $form): array
    {
        $options = [
            'sign_type' => $this->config->get('sign_type', 'RSA'),
            'out_no'    => $form->get('order_id'),
        ];

        if (!empty($form->get('trade_sn'))) {
            $options['ord_no'] = $form->get('trade_sn');
        }

        $this->client->cancelOrder($options);

        return [];
    }

    /**
     * @param Query $form
     *
     * @return array
     */
    public function query(Query $form): array
    {
        $options = [
            'out_no' => $form->get('order_id'),
        ];

        if (!empty($form->get('trade_sn'))) {
            $options['ord_no'] = $form->get('trade_sn');
        }

        $response = $this->client->getOrderInfo($options);

        return $this->doQuery($response, $form);
    }

    /**
     * @param array $receives
     *
     * @return array
     */
    public function refundNotify(array $receives): array
    {
        throw new GatewayMethodNotSupportException('Pingan channels are not supported to send refund notify');
    }

    /**
     * @param array $receives
     *
     * @return array
     */
    public function closeNotify(array $receives): array
    {
        return [];
    }

    /**
     * @param array $receives
     *
     * @return bool
     */
    public function verify($receives): bool
    {
        return $this->client->verifyResponse($receives);
    }

    /**
     * @return string
     */
    public function success(): string
    {
        return $this->client->success();
    }

    /**
     * @return string
     */
    public function fail(): string
    {
        return $this->client->failed();
    }

    /**
     * @return mixed
     */
    public function receiveNotificationFromRequest()
    {
        return $_POST;
    }

    /**
     * @param $receives
     *
     * @return array
     */
    public function convertNotificationToArray($receives): array
    {
        return $receives;
    }

    /**
     * @param Charge $form
     *
     * @return array
     */
    abstract protected function prepareCharge(Charge $form): array;

    /**
     * @param array  $response
     * @param Charge $form
     *
     * @return array
     */
    abstract protected function doCharge(array $response, Charge $form): array;

    /**
     * @return string
     */
    abstract protected function getTradeType(): string;

    abstract protected function doQuery(array $response, Query $form) : array;

    /**
     * @param string $payTime
     *
     * @return string
     */
    protected function normalizePayTime($payTime) : string
    {
        preg_match('/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', $payTime, $matches);

        return "$matches[1]-$matches[2]-$matches[3] $matches[4]:$matches[5]:$matches[6]";
    }

    /**
     * convert the date string to timestamp
     * @param string $date
     * @param int $timezone
     * @return int
     */
    protected function date2timestamp($date, $timezone = 8)
    {
        preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $date, $matches);
        $tmpTimestamp = (new DateTime("$matches[1]-$matches[2]-$matches[3] $matches[4]:$matches[5]:$matches[6]", new DateTimeZone('UTC')))->getTimestamp();

        return $tmpTimestamp - $timezone * 3600;
    }


    /**
     * @param int $status
     *
     * @return string
     */
    protected function normalizeStatus($status) : string
    {
        switch ($status) {
            case Client::ORDER_STATUS_SUCCESS:
                return 'paid';
                break;
            case Client::ORDER_STATUS_CANCELED:
                return 'closed';
                break;
            default:
                return 'created';
        }
    }
}
