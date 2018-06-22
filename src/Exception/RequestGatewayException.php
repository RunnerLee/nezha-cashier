<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-05
 */

namespace Runner\NezhaCashier\Exception;

use GuzzleHttp\Exception\RequestException;
use RuntimeException;

class RequestGatewayException extends RuntimeException
{
    protected $responseRaw;

    public function __construct($message, RequestException $exception)
    {
        if (!is_null($exception->getResponse())) {
            $this->responseRaw = (string) $exception->getResponse()->getBody();
        }

        parent::__construct($message, 0, $exception);
    }
}
