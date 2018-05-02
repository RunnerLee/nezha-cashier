<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Gateways\Wechat;

use FastD\Http\Request;
use Runner\NezhaCashier\Exception\GatewayException;
use Runner\NezhaCashier\Requests\Charge;

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
        $request = (new Request('GET', $url))->withOption(CURLOPT_REFERER, $this->config->get('site_url'));
        if ($this->config->get('spider_by_proxy', false)) {
            $request->withOptions(
                [
                    CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
                    CURLOPT_PROXY => $this->config->get('spider_proxy'),
                ]
            );
        }
        $response = $request->send(
            [],
            [
                'Cookie: pgv_pvi=6107464704; pgv_pvid=1421357157',
                'Host: wx.tenpay.com',
                'Accept-Language: en,zh-CN;q=0.8,zh;q=0.6,en-US;q=0.4',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Upgrade-Insecure-Requests: 1',
            ]
        );
        $match = [];
        if (0 === preg_match('/var\surl="(.*?)"/', (string) $response->getBody(), $match) || empty($match[1])) {
            throw new GatewayException('Wechat Gateway Error: failed to spider wechat charge page');
        }

        return urldecode($match[1]);
    }
}
