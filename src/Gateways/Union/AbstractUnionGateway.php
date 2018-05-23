<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Gateways\Union;

use DateTime;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Runner\NezhaCashier\Exception\GatewayException;
use Runner\NezhaCashier\Exception\GatewayMethodNotSupportException;
use Runner\NezhaCashier\Exception\RequestGatewayException;
use Runner\NezhaCashier\Gateways\AbstractGateway;
use Runner\NezhaCashier\Requests\Close;
use Runner\NezhaCashier\Requests\Refund;
use Runner\NezhaCashier\Utils\HttpClient;

abstract class AbstractUnionGateway extends AbstractGateway
{
    const WEB_CHARGE = 'https://gateway.95516.com/gateway/api/frontTransReq.do';

    const WEB_QUERY_ORDER = 'https://gateway.95516.com/gateway/api/backTransReq.do';

    const APP_CHARGE = 'https://gateway.95516.com/gateway/api/appTransReq.do';

    /**
     * 退款.
     *
     * @param Refund $form
     *
     * @return array
     */
    public function refund(Refund $form): array
    {
        throw new GatewayMethodNotSupportException();
    }

    /**
     * 关闭.
     *
     * @param Close $form
     *
     * @return array
     */
    public function close(Close $form): array
    {
        throw new GatewayMethodNotSupportException();
    }

    /**
     * @param $receives
     *
     * @return array
     */
    public function chargeNotify(array $receives): array
    {
        return [
            'order_id' => $receives['orderId'],
            'status' => 'paid',
            'trade_sn' => $receives['queryId'],
            'buyer_identifiable_id' => '',
            'amount' => $receives['settleAmt'],
            'buyer_name' => '',
            'paid_at' => DateTime::createFromFormat('mdHis', $receives['traceTime'])->getTimestamp(),
            'raw' => $receives,
        ];
    }

    /**
     * 退款通知, 并非所有支付渠道都支持
     *
     * @param $receives
     *
     * @return array
     */
    public function refundNotify(array $receives): array
    {
        throw new GatewayMethodNotSupportException();
    }

    /**
     * 关闭通知, 并非所有支付渠道都支持
     *
     * @param $receives
     *
     * @return array
     */
    public function closeNotify(array $receives): array
    {
        throw new GatewayMethodNotSupportException();
    }

    /**
     * 通知校验.
     *
     * @param
     *
     * @return bool
     */
    public function verify($receives): bool
    {
        $publicKey = '.pem' === substr($this->config->get('union_public_key'), -4)
            ? openssl_get_publickey("file://{$this->config->get('union_public_key')}")
            : $this->config->get('union_public_key');

        return 1 === openssl_verify(
            $this->formatParameters($receives),
            base64_decode($receives['signature']),
            $publicKey,
            OPENSSL_ALGO_SHA1
        );
    }

    /**
     * 通知成功处理响应.
     *
     * @return string
     */
    public function success(): string
    {
        return 'ok.';
    }

    /**
     * 通知处理失败响应.
     *
     * @return string
     */
    public function fail(): string
    {
        return 'failed.';
    }

    /**
     * @return array
     */
    public function receiveNotificationFromRequest(): array
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
     * @param array $parameters
     *
     * @return array
     */
    protected function createPayload(array $parameters): array
    {
        $parameters = array_merge(
            [
                'version' => '5.0.0',
                'encoding' => 'UTF-8',
                'certId' => $this->config->get('cert_id'),
                'merId' => $this->config->get('mer_id'),
                'accessType' => '0',
                'signMethod' => '01',
                'bizType' => '000000',
            ],
            $parameters
        );
        $parameters['signature'] = $this->sign($parameters);

        return $parameters;
    }

    protected function sign(array $parameters): string
    {
        $signature = '';

        $privateKey = '.pem' === substr($this->config->get('app_private_key'), -4)
            ? openssl_get_privatekey("file://{$this->config->get('app_private_key')}")
            : $this->config->get('app_private_key');

        openssl_sign(
            $this->formatParameters($parameters),
            $signature,
            $privateKey,
            OPENSSL_ALGO_SHA1
        );

        return base64_encode($signature);
    }

    protected function formatParameters(array $parameters): string
    {
        unset($parameters['signature']);
        ksort($parameters);

        return sha1(urldecode(http_build_query($parameters)), false);
    }

    protected function parseResponse($response): array
    {
        $content = [];
        /*
         * 这里需要手动切割字符串
         * 防止签名中含有特殊字符被 urldecode() 或 parse_str() 处理, 例如签名中含有+号会被转换成空格, 导致验签失败
         */
        array_map(
            function ($item) use (&$content) {
                false === strpos($item, '=') && ($item .= '=');
                list($name, $value) = explode('=', $item);
                $content[$name] = $value;
            },
            explode('&', $response)
        );

        return $content;
    }

    /**
     * @param $status
     *
     * @return string
     */
    protected function formatTradeStatus($status): string
    {
        switch ($status) {
            case '05':
                return 'created';
            default:
                return 'paid';
        }
    }

    /**
     * @param $url
     * @param array $parameters
     *
     * @return array
     */
    protected function request($url, array $parameters): array
    {
        return HttpClient::request(
            'POST',
            $url,
            [
                RequestOptions::FORM_PARAMS => $parameters,
            ],
            function (ResponseInterface $response) {
                $result = $this->parseResponse((string) $response->getBody());

                if ('00' !== $result['respCode']) {
                    throw new GatewayException(
                        'Union Gateway Error:'.$result['respMsg'],
                        $result
                    );
                }

                return $result;
            },
            function (RequestException $exception) {
                throw new RequestGatewayException('Union Gateway Error.', $exception);
            }
        );
    }
}
