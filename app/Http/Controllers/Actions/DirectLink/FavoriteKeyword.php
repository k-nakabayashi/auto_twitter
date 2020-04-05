<?php

namespace App\Http\Controllers\Actions\DirectLink;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Domain\Services\ApiService\Tw\RequestToken;
use App\Domain\Services\InitalActionService;

class FavoriteKeyword extends Controller
{
    use InitalActionService;
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        //
        return view('favorite_keyword');
    }
}
