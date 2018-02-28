<?php

namespace Nh\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class EntrustRole
{
    protected $auth;

    /**
     * Creates a new instance of the middleware.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Closure $next
     * @param  $roles
     * @return mixed
     */
    public function handle($request, Closure $next, $roles)
    {
        if ($this->auth->guest() || !$request->user()->hasRole(explode('|', $roles))) {
            if ($request->expectsJson()) {
                $response = [
                    'code' => 403,
                    'status' => 'error',
                    'data' => ['error' => 'You not permission .'],
                ];

                return response()->json($response, $response['code']);
            }

            abort(403);
        }

        return $next($request);
    }
}
