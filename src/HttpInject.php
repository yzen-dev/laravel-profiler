<?php

namespace Profiler;

use Error;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;

class HttpInject
{
    /**
     * The App container
     *
     * @var Container
     */
    protected $container;


    /**
     * The URIs that should be excluded.
     *
     * @var array
     */
    protected $except = [];

    /**
     * Create a new middleware instance.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Handle an incoming request.
     *
     * @param JsonResponse $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        try {
            /** @var \Illuminate\Http\Response $response */
            $response = $next($request);
        } catch (Exception $e) {
            $response = $this->handleException($request, $e);
        } catch (\Throwable $e) {
            $response = $this->handleException($request, $e);
        }

        Analyze::getInstance()->setRequest($request);

        if ($response instanceof \Symfony\Component\HttpFoundation\JsonResponse) {
            $content = $response->getContent();
            if (is_string($content) && !empty($content)) {
                $content = json_decode($content);
                if (is_object($content)) {
                    if (isset($content->exception) && is_string($content->exception)) {
                        $content->exception = [
                            'error' => $content->exception,
                            'file' => $content->file,
                            'line' => $content->line,
                            'message' => $content->message,
                            'trace' => $content->trace,
                        ];
                        unset($content->file);
                        unset($content->line);
                        unset($content->message);
                        unset($content->trace);
                    }
                    $content->debug = Analyze::getInstance()->getAnalyze();

                    $response->setData($content);
                }
            }
        }


        return $response;
    }

    /**
     * Handle the given exception.
     *
     * (Copy from Illuminate\Routing\Pipeline by Taylor Otwell)
     *
     * @param $passable
     * @param Exception $e
     *
     * @return mixed
     * @throws Exception
     */
    protected function handleException(
        $passable, Exception $e
    )
    {
        if (!$this->container->bound(ExceptionHandler::class) || !$passable instanceof Request) {
            throw $e;
        }

        $handler = $this->container->make(ExceptionHandler::class);

        $handler->report($e);

        return $handler->render($passable, $e);
    }
}
