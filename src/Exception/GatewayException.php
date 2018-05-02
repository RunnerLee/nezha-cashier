<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-01
 */

namespace Runner\NezhaCashier\Exception;

use RuntimeException;

class GatewayException extends RuntimeException
{
    public $raw;

    public function __construct(string $message = '', $raw = null)
    {
        $this->raw = $raw;

        parent::__construct($message, 0, null);
    }
}
