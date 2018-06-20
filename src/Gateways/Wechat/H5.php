<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Gateways\Wechat;

use Runner\NezhaCashier\Requests\Charge;
use Runner\NezhaCashier\Utils\Traits\WechatH5Spider;

class H5 extends AbstractWechatGateway
{
    use WechatH5Spider;

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
}
