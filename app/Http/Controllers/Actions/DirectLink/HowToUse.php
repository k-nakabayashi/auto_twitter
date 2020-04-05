<?php

namespace App\Http\Controllers\Actions\DirectLink;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Domain\Services\ApiService\Tw\RequestToken;
use App\Domain\Services\InitalActionService;

class HowToUse extends Controller
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
    public function __invoke(Request $req, RequestToken $req_token)
    {
        //
        return view('/how_to_use');
    }
}
