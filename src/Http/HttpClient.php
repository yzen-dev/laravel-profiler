<?php

namespace Profiler\Http;

use GuzzleHttp\Client;
use function event;

/**
 * Class Http
 *
 * @author yzen.dev <yzen.dev@gmail.com>
 */
class HttpClient extends Client
{
    /**
     * @param       $method
     * @param       $uri
     * @param array $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request($method, $uri = '', array $options = [])
    {
        $startTime = microtime(true);
        $data = parent::request($method, $uri, $options);
        $requestTime = microtime(true) - $startTime;
        event(new HttpRequestExecuted($uri, $method, $options, $startTime, $requestTime));
        return $data;
    }
}
