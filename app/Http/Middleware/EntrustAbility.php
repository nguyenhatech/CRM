<?php

namespace Nh\Http\Middleware;
use Illuminate\Contracts\Auth\Guard;
use Closure;

class EntrustAbility
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
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @param $roles
     * @param $permissions
     * @param bool $validateAll
     * @return mixed
     */
    public function handle($request, Closure $next, $roles, $permissions, $validateAll = false)
    {
        if ($request->user()->isSuperAdmin()) {
            return $next($request);
        }

        if ($this->auth->guest()
            || !$request->user()->ability(
                explode('|', $roles),
                explode('|', $permissions),
                ['validate_all' => $validateAll]
            )
        ) {
            if ($request->expectsJson()) {
                $response = [
                    'code' => 403,
                    'status' => 'error',
                    'data' => ['error' => 'You not permission.'],
                ];

                return response()->json($response, $response['code']);
            }

            abort(403);
        }

        return $next($request);
    }
}
