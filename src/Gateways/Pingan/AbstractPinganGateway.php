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
}