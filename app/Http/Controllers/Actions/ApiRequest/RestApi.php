<?php
//全テーブルのCrud処理を行います。
namespace App\Http\Controllers\Actions\ApiRequest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Domain\Repogitories\RESTfulDAO\CommonDAOInterface;
use Illuminate\Validation\Factory;
use Log;
use App\Facades\ErrorService;

class RestApi extends Controller
{

    private $domain;
    private $factory;
    private $model;

    public function __construct(
        CommonDAOInterface $domain, 
        Factory $factory,
        Request $req
        )
    {  
        Log::debug(json_encode($req->all()));
        if (!empty($req->input("model_name"))) {
            Log::debug($req->input("model_name"));
            
            $this->domain = $domain;
            $this->factory =$factory;
            $model_name = $req->input("model_name");
            $this->model = app("App"."\\".$model_name);
        }
       
    }



    public function index(Request $req)
    {
        
        $model = "";
    
        //ログイン中のidで絞り込んで取得
        if (!empty($req->input("id"))) {

            $model =  $this->domain->getAll_My_Data($this->model);

        } else if (!empty($req->input("column"))) {

            $model =  $this->domain->getDataBy($this->model);

        } else {
            //文字通り全てを取得
            
            $model = $this->domain->index($this->model);
        }
        // return $model->toJson();
        return response()->json(['data' => $model, 'errors' => ErrorService::getMessage() ]);
    }



    public function create(Request $request)
    {

        $validator = $this->factory->make($request->all(), $this->model->getColums_for_Create());
        if ($validator->fails()) {

    
            ErrorService::setMessage('・選択不足の項目があります。');
            return response()->json(['errors' => ErrorService::getMessage()]);
        }

   
        $model = $this->domain->create($this->model, $this->model->getNo_Duplication());
        return response()->json(['data' =>  $model, 'errors' => ErrorService::getMessage() ]);
    }


    public function show(Request $request)
    {
        
    //    log::debug($this->domain);
        
        $validator = $this->factory->make($request->all(), $this->model->getColums_for_show());
        if ($validator->fails()) {
            ErrorService::setMessage('・選択不足の項目があります。');
            return response()->json(['errors' => ErrorService::getMessage()]);
        }
        $result = $this->domain->show($this->model);
        return $result->toJson();

        return response()->json(['data' =>  $result, 'errors' => ErrorService::getMessage() ]);

    }

    public function edit(Request $request)
    {
        //
       
        $validator = $this->factory->make($request->all(), $this->model->getColums_for_Edit());
        if ($validator->fails()) {
            ErrorService::setMessage('・選択不足の項目があります。');
            return response()->json(['errors' => ErrorService::getMessage()]);
        }
        $model = $this->domain->edit($this->model);
        return response()->json(['data' => $model, 'errors' => ErrorService::getMessage() ]);
    }
    public function destroy(Request $request)
    {
        //
        Log::debug('Delete : '.$request->input("model_name"));
        $validator = $this->factory->make($request->all(), $this->model->getColums_for_Destroy());
        if ($validator->fails()) {
            ErrorService::setMessage('・選択不足の項目があります。');
            return response()->json(['errors' => ErrorService::getMessage()]);
        }
        
        $this->domain->destroy($this->model);
        return response()->json(['errors' => ErrorService::getMessage(), ]);
    

    }
}
