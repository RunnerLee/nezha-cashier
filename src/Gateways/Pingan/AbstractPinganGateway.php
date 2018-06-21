<?php
namespace Runner\NezhaCashier\Gateways\Pingan;

use Runner\NezhaCashier\Gateways\AbstractGateway;
use Runner\NezhaCashier\Requests\Charge;
use Runner\NezhaCashier\Requests\Close;
use Runner\NezhaCashier\Requests\Query;
use Runner\NezhaCashier\Requests\Refund;
use Runner\NezhaCashier\Utils\Config;
use Wangjian\PinganPay\Client;

abstract class AbstractPinganGateway extends AbstractGateway
{
    /**
     * pingan payment client
     * @var Client
     */
    protected $client;

    /**
     * AbstractPinganGateway constructor.
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

        if(!is_null($privateKeyPath = $this->config->get('private_key', null))) {
            $this->client->setPrivateKey($privateKeyPath);
        }
    }

    /**
     * @param Charge $form
     * @return array
     */
    public function charge(Charge $form): array
    {
        if($form->get('currency') != 'CNY') {
            throw new \InvalidArgumentException('only the CNY currenry is supported');
        }

        $options = array_merge([
            'out_no' => $form->get('order_id'),
            'pmt_tag' => $this->config->get('pmt_tag'),
            'original_amount' => $form->get('amount'),
            'trade_amount' => $form->get('amount'),
            'ord_name' => $form->get('subject'),
            'trade_account' => $this->config->get('trade_account', null),
            'remark' => $form->get('description'),
            'notify_url' => $this->config->get('notify_url'),
            'tag' => $this->config->get('tag', null)
        ], $this->prepareCharge($form));

        $response = $this->client->charge($options);

        return $this->doCharge($response, $form);
    }

    public function refund(Refund $form): array
    {
        $options = [
            'sign_type' => $this->config->get('sign_type', 'RSA'),
            'out_no' => $form->get('order_id'),
            'refund_out_no' => $form->get('refund_id'),
            'refund_amount' => $form->get('refund_amount'),
            'shop_pass' => $this->config->get('shop_pass', null),
            'trade_account' => $this->config->get('trade_account', null),
        ];

        if(!empty($form->get('trade_no'))) {
            $options['trade_no'] = $form->get('trade_no');
        }

        return $this->client->refund($options);
    }

    public function close(Close $form): array
    {
        // TODO: Implement close() method.
    }

    public function query(Query $form): array
    {
        // TODO: Implement query() method.
    }

    public function chargeNotify(array $receives): array
    {
        $tradeResult = json_decode($receives['trade_result'], true);
        return [
            'order_id' => $receives['out_no'],
            'status' => 'paid', // 微信只推送支付完成
            'trade_sn' => $receives['ord_no'],
            'buyer_identifiable_id' => $tradeResult['openid'],
            'buyer_is_subscribed' => 'N' === $tradeResult['is_subscribe'] ? 'no' : 'yes',
            'amount' => $receives['amount'],
            'buyer_name' => '',
            'paid_at' => $this->normalizePayTime($receives['pay_time']),
            'raw' => $receives,
        ];
    }

    public function refundNotify(array $receives): array
    {
    }

    public function closeNotify(array $receives): array
    {
    }

    public function verify($receives): bool
    {
        return $this->client->verifyResponse($receives);
    }

    public function success(): string
    {
        return $this->client->success();
    }

    public function fail(): string
    {
        return $this->client->failed();
    }

    public function receiveNotificationFromRequest()
    {
        return $_POST;
    }

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

    protected function normalizePayTime($payTime)
    {
        preg_match('/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', $payTime, $matches);
        return "$matches[1]-$matches[2]-$matches[3] $matches[4]:$matches[5]:$matches[6]";
    }
}