<?php

namespace Nh\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->expectsJson() && $exception instanceof NotFoundException) {
            $response = [
                'code' => 404,
                'status' => 'error',
                'data' => 'Resource not found',
                'message' => 'Not Found',
            ];

            return response()->json($response, $response['code']);
        }

        $response = parent::render($request, $exception);
        if ($request->is('api/*')) {
            // app('Barryvdh\Cors\CorsService')->addActualRequestHeaders($response, $request);
            // if ($exception instanceof \ErrorException && $request->expectsJson()) {
            //     return response()->json([
            //         "code"    => 500,
            //         "message" => $exception->getMessage(),
            //         "file"    => $exception->getFile(),
            //         "line"    => $exception->getLine()
            //     ], 500);
            // }
        }

        return $response;
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            $response = [
                'code' => 401,
                'status' => 'error',
                'data' => ['error' => 'Unauthenticated.'],
            ];

            return response()->json($response, $response['code']);
            // return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        if ($request->is('admin*')) {
            return redirect()->guest('/admin/login');
        }

        return redirect()->guest('login');
    }
}
