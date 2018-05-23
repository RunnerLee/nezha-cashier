<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-05
 */

namespace Runner\NezhaCashier\Utils;

use GuzzleHttp\Client;

class HttpClient
{
    public static function request(
        $method,
        $url,
        array $options = [],
        callable $onFulfilled = null,
        callable $onRejected = null
    ) {
        $client = new Client();

        $response = $client->requestAsync($method, $url, $options)->then(null, $onRejected)->wait();

        return is_null($onFulfilled) ? $response : call_user_func($onFulfilled, $response);
    }
}
