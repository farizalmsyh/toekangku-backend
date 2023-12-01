<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class SanctumIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $bearer = $request->bearerToken();
        if ($bearer && str_contains($bearer, '|')) {
            $bearer = explode("|",$bearer)[1];
            if ($token = DB::table('personal_access_tokens')->where('token', hash('sha256',$bearer))->first())
            {
                if ($user = User::find($token->tokenable_id))
                {
                    Auth::login($user);
                    return $next($request);
                }
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated!',
        ], 401);
    }
}
