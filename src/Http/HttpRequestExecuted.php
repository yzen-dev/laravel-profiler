<?php

namespace Profiler\Http;

/**
 * Class HttpRequestExecuted
 *
 * @author yzen.dev <yzen.dev@gmail.com>
 */
class HttpRequestExecuted
{

    /**
     * The http query that was executed.
     *
     * @var string
     */
    public $url;
    
    /** @var string */
    public $method;
    
    /**
     * The data of query.
     *
     * @var array
     */
    public $data;
    
    /**
     * The number of milliseconds it took to execute the query.
     *
     * @var float
     */
    public $time;
    
    public $startTime;
    
    public $response;
    
    /**
     * Create a new event instance.
     *
     * @param string $url
     * @param array $data
     * @param float $time
     */
    public function __construct($url, $method, $data, $startTime, $time, $response = null)
    {
        $this->url = $url;
        $this->data = $data;
        $this->method = $method;
        $this->startTime = $startTime;
        $this->time = $time;
        $this->response = $response;
    }
}
