<?php

namespace App\Http\Controllers\Actions\AutoFunction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Domain\Services\TweetService\TweetStart;
use Illuminate\Validation\Factory;
use Log;
use App\Facades\ErrorService;

class TweetStartAction extends Controller
{
    private $domain;
    private $factory;
    private $model;

    public function __construct(
        TweetStart $domain, 
        Factory $factory,
        Request $req
        )
    {  
        Log::debug(json_encode($req->all()));
        if (!empty($req->input("model_name"))) {
            Log::debug($req->input("model_name"));
            
            $this->domain = $domain;
            $this->factory =$factory;
            $model_name = $req->input("model_name");//Tw_Tweet
            $this->model = app("App"."\\".$model_name);
        }
       
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $request = app('Illuminate\Http\Request');
        Log::debug('action');
        $validator = $this->factory->make($request->all(), $this->model->getColums_for_Create());
        if ($validator->fails()) {

    
            ErrorService::setMessage('・選択不足の項目があります。');
            return response()->json(['errors' => ErrorService::getMessage()]);
        }

        $model = $this->domain->__invoke($this->model);
        return response()->json(['data' =>  $model, 'errors' => ErrorService::getMessage() ]);

    }
}
