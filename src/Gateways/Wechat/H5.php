<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Gateways\Wechat;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Runner\NezhaCashier\Exception\GatewayException;
use Runner\NezhaCashier\Exception\RequestGatewayException;
use Runner\NezhaCashier\Requests\Charge;
use Runner\NezhaCashier\Utils\HttpClient;

class H5 extends AbstractWechatGateway
{
    /**
     * @param Charge $form
     *
     * @return array
     */
    protected function prepareCharge(Charge $form): array
    {
        $content = [
            'scene_info' => json_encode(
                [
                    'h5_info' => [
                        'type' => 'Wap',
                        'wap_url' => $this->config->get('site_url'),
                        'wap_name' => $this->config->get('site_name'),
                    ],
                ]
            ),
        ];
        if ($this->config->get('spider')) {
            $ip = $this->config->get('spider_ip');
            $this->config->get('spider_by_proxy') && $ip = $this->config->get('spider_proxy_ip');
            $content['spbill_create_ip'] = $ip;
        }

        return $content;
    }

    protected function doCharge(array $response, Charge $form): array
    {
        $url = $response['mweb_url'];
        if (!$this->config->get('spider', false)) {
            $url .= '&redirect_url='.urlencode($form->get('return_url'));
        } else {
            $url = $this->spider($url);
        }

        return [
            'charge_url' => $url,
        ];
    }

    protected function getTradeType(): string
    {
        return 'MWEB';
    }

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
