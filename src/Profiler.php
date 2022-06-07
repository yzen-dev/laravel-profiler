<?php

namespace Profiler;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Contracts\Foundation\Application;
use Profiler\Http\HttpRequestExecuted;

/**
 * Class Profiler
 *
 * @author yzen.dev <yzen.dev@gmail.com>
 */
class Profiler
{
    /**
     * The Laravel application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @param Application $app
     */
    public function __construct($app = null)
    {
        if (!$app) {
            $app = app();   //Fallback when $app is not given
        }
        $this->app = $app;
        Analyze::getInstance()->projectDir = str_replace('/public', '', $_SERVER['DOCUMENT_ROOT']);
    }

    /**
     * Enable the profiler
     */
    public function isEnabled()
    {
        $debugMode = Cookie::get('debug-mode') ?? false;

        return env('LOAD_DEBUG') && $debugMode == 'true';
    }

    /**
     * Boot the profiler
     */
    public function boot()
    {
        if (!$this->isEnabled()) {
            return;
        }
        Analyze::getInstance()->startTime = $_SERVER["REQUEST_TIME_FLOAT"];

        $this->app->booted(
            function () {
                $time = microtime(true) - Analyze::getInstance()->startTime;
                Analyze::getInstance()->bootTime = $time;
                $data = [
                    'type' => 'boot',
                    'title' => 'App booted',
                    'startTime' => Analyze::getInstance()->startTime,
                    'time' => $time,
                ];
                Breadcrumbs::add($data);
            }
        );

        $this->app[Kernel::class]->pushMiddleware(HttpInject::class);

        $this->app->events->listen(QueryExecuted::class, function (QueryExecuted $query) {
            $this->addHttpRequest($query);
        });

        $this->app->events->listen(HttpRequestExecuted::class, function (HttpRequestExecuted $request) {
            $this->addHttpRequest($request);
        });
    }

    /**
     * @param $request
     *
     * @return void
     */
    public function addSqlRequest(QueryExecuted $query)
    {
        $time = 0;
        if ($query->time !== null) {
            $time = $query->time / 1000.0;
        }
        $query = vsprintf(str_replace(['?'], ['\'%s\''], $query->sql), $query->bindings);
        $data = [
            'type' => 'sql',
            'title' => 'SQL query',
            'time' => $time,
            'startTime' => microtime(true) - $time,
            'stacktrace' => self::prepareTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS | DEBUG_BACKTRACE_PROVIDE_OBJECT, 7)),
            'meta' => [
                'query' => $query,
            ],
        ];

        Breadcrumbs::add($data);
    }
    
    /**
     * @param $request
     *
     * @return void
     */
    public function addHttpRequest($request)
    {
        $data = [
            'type' => 'http',
            'title' => 'HTTP query',
            'time' => $request->time,
            'method' => $request->method,
            'startTime' => $request->startTime,
            'meta' => [
                'url' => $request->url,
                'data' => $request->data,
            ],
        ];
        Breadcrumbs::add($data);
    }

    /**
     * @param $backtrace
     *
     * @return array
     */
    public static function prepareTrace($backtrace)
    {
        $resultStackTrace = [];
        foreach ($backtrace as $index => $trace) {
            if (isset($trace['file'])) {
                $resultStackTrace[] = [
                    'file' => str_replace(Analyze::getInstance()->projectDir, '', $trace['file']),
                    'vendor' => preg_match('/vendor\//', $trace['file']),
                    'line' => $trace['line'],
                ];
            }
        }
        return $resultStackTrace;
    }
}
