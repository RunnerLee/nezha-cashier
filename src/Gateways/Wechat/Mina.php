<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Gateways\Wechat;

use FastD\Http\Request;
use Runner\NezhaCashier\Exception\GatewayException;
use Runner\NezhaCashier\Exception\WechatOpenIdException;

class Mina extends Official
{
    const JSAPI_AUTH_URL = 'https://api.weixin.qq.com/sns/jscode2session';

    protected function getOpenId($code): string
    {
        $parameters = [
            'appid' => $this->config->get('app_id'),
            'secret' => $this->config->get('app_secret'),
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ];

        $response = (new Request('GET', static::JSAPI_AUTH_URL))->send($parameters);

        if (!$response->isSuccessful()) {
            throw new GatewayException('Wechat Gateway Error.', (string) $response->getBody());
        }

        $result = json_decode($response->getBody(), true);

        if (isset($result['errcode'])) {
            throw new WechatOpenIdException($result['errmsg']);
        }

        return $result['openid'];
    }
}
