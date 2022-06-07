<?php

namespace Profiler;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Profiler\Processors\MemoryProcessor;
use Profiler\Processors\GitInfoProcessor;

/**
 * Class Analyze
 *
 * @package Profiler
 */
class Analyze
{
    public $phpVersion = null;
    public $startTime = null;
    public $bootTime = null;
    public $executedTime = null;
    public $user = null;
    public $projectDir = null;
    public $url = null;
    
    public $request = null;
    public $clientIp = null;
    public $server = null;
    public $session = null;
    
    public $breadcrumbs;
    
    /**
     * @var \Profiler\Analyze|null
     */
    private static $instance;
    
    public static function getInstance(): Analyze
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        
        return static::$instance;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function setRequest($request){
        $this->user = Auth::user();
        $this->session = $request->hasSession() ? $request->session() : null;
        $this->request = new \stdClass();
        $this->request->path = $request->path();
        $this->request->fullPath = $request->url();
        $this->request->method = $request->getMethod();
        $this->request->cookie = $request->cookies;
        $this->request->query_params = $request->query;
        $this->request->headers = $request->headers;
        $this->request->resolver = $request->getRouteResolver;
        $this->clientIp = $request->getClientIp();
        $this->server = $request->server->all();
    }
    
    /**
     * Get all breadcrumbs
     *
     * @return array<mixed>
     */
    public function getAnalyze(): array
    {
        return [
            'php' => PHP_VERSION,
            'laravel' => App::version(),
            'git' => GitInfoProcessor::get(),
            'memoryPeakUsage' => MemoryProcessor::getMemoryPeakUsage(),
            'breadcrumbs' => Breadcrumbs::getBreadcrumbs(),
            'user' => $this->user,
            'session' => $this->session,
            'url' => $this->url,
            'projectDir' => $this->projectDir,
            'request' => $this->request,
            'server' => $this->server,
            'startTime' => $this->startTime,
            'executedTime' => microtime(true) - $this->startTime,
        ];
    }
}
