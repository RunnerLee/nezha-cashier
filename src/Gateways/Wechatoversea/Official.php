<?php
namespace Runner\NezhaCashier\Gateways\Wechatoversea;

use Runner\NezhaCashier\Requests\Charge;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Runner\NezhaCashier\Exception\RequestGatewayException;
use Runner\NezhaCashier\Exception\WechatOpenIdException;
use Runner\NezhaCashier\Utils\HttpClient;

class Official extends AbstractWechatoverseaGateway
{
    public function prepareCharge(Charge $form): array
    {
        $openId = $form->has('extras.open_id')
            ? $form->get('extras.open_id')
            : $this->getOpenId($form->get('extras.code'));

        return [
            'openid' => $openId,
        ];
    }

    public function doCharge(array $response, Charge $form): array
    {
        return $response;
    }

    protected function getOpenId($code): string
    {
        $parameters = [
            'appid'      => $this->config->get('app_id'),
            'secret'     => $this->config->get('app_secret'),
            'code'       => $code,
            'grant_type' => 'authorization_code',
        ];

        return HttpClient::request(
            'GET',
            static::MP_JSAPI_AUTH_URL,
            [
                RequestOptions::QUERY => $parameters,
            ],
            function (ResponseInterface $response) {
                $result = json_decode($response->getBody(), true);

                if (isset($result['errcode'])) {
                    throw new WechatOpenIdException($result['errmsg']);
                }

                return $result['openid'];
            },
            function (RequestException $exception) {
                throw new RequestGatewayException('Wechat Gateway Error', $exception);
            }
        );
    }
}