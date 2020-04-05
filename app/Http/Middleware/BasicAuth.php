<?php

namespace App\Http\Middleware;

use Closure;
// use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;

class BasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);

        // // ベーシック認証で入力されたIDとPASS
        // $user = $request->getUser();
        // $pass = $request->getPassword();

        // // IDとPASSが一致していればコントローラのアクションへ
        // // 例：ID → laraweb / pass → laraweb
        // if($user == 'laraweb' && $pass = 'laraweb'){
        //     return $next($request);
        // }

        // // 間違っていたら401
        // $headers = ['WWW-Authenticate' => 'Basic'];
        // return new Response('Invalid credentials.', 401, $headers);
    }
}
