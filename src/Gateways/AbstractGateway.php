<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Gateways;

use Runner\NezhaCashier\Contracts\GatewayInterface;
use Runner\NezhaCashier\Utils\Config;

abstract class AbstractGateway implements GatewayInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * AbstractGateway constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * 格式化消息.
     *
     * @param $receives
     *
     * @return array
     */
    abstract public function convertNotificationToArray($receives): array;
}
