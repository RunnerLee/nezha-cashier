<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-06
 */

namespace Runner\NezhaCashier\Utils\Traits;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Runner\NezhaCashier\Exception\GatewayException;
use Runner\NezhaCashier\Exception\RequestGatewayException;
use Runner\NezhaCashier\Utils\HttpClient;

trait WechatH5Spider
{
    /**
     * @param $url
     *
     * @return string
     */
    public function spider($url): string
    {
        $options = [
            RequestOptions::HEADERS => [
                'Referer' => $this->config->get('site_url'),
                'Host' => 'wx.tenpay.com',
                'Accept-Language' => 'en,zh-CN;q=0.8,zh;q=0.6,en-US;q=0.4',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Upgrade-Insecure-Requests' => '1',
            ],
        ];
        if ($this->config->get('spider_by_proxy', false)) {
            $options[RequestOptions::PROXY] = $this->config->get('spider_proxy');
        }

        return HttpClient::request(
            'GET',
            $url,
            $options,
            function (ResponseInterface $response) {
                $match = [];
                if (0 === preg_match('/var\surl="(.*?)"/', (string) $response->getBody(), $match) || empty($match[1])) {
                    throw new GatewayException('Wechat Gateway Error: failed to spider wechat charge page');
                }

                return urldecode($match[1]);
            },
            function (RequestException $exception) {
                throw new RequestGatewayException('spider wechat h5 payment page failed', $exception);
            }
        );
    }
}
