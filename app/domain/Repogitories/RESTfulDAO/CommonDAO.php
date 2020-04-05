<?php
//全テーブルの基本Crud処理を行います。
//主にRestApiというコントーラーの中で呼ばれます。
//各種引数に使用するモデルを渡し、そのモデルを使ったCrud処理を行います。

namespace App\Domain\Repogitories\RESTfulDAO;

use Illuminate\Support\Facades\Request;
use Log;
use App\Facades\ErrorService;

class CommonDAO implements CommonDAOInterface
{


    //全データを取得
    //カラム名の配列をリクエストクエリに仕込むと、指定したカラムでデータを取得することができます。
    public function index($model)
    {
        try {
            $select_columns = Request::input("select_columns");
            
            $all_data = array();

            if (empty($select_columns)) {
              
                $all_data = $model->where("deleted_at", null)->all();
       
            } else {
            
                $all_data = $model->where("deleted_at", null)->select($select_columns)->get();
            }
            return $all_data;
        } catch (\Exception $e) {
            Log::debug($e);
            Log::debug("エラー発生：予期せぬエラーが起きました。");
            ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
        } 
    }

    //ログイン中のユーザーが保持するレコードを全て取得します。
    public function getAll_My_Data($model)
    {
        try {
            $data = Request::all();

            if (!empty($data['model_name'])) {
                unset($data['model_name']);
            }

            $id = $data["id"];
            $all_data = array();

            if (!array_key_exists("select_columns",$data)) {
            
                Log::debug(" id :".$id);
                $all_data = $model->where("app_id", $id)->where("deleted_at", null)->get();

            } else {
                Log::debug(" select ");
                $all_data = $model->select($data["select_columns"])->where("app_id", $id )->where("deleted_at", null)->get();
            }
            return $all_data;

        } catch (\Exception $e) {
            Log::debug($e);
            Log::debug("エラー発生：予期せぬエラーが起きました。");
            ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
        } 
    }

    //全プライマリーキーの配列を返します。
    public function getAll_ID($model)
    {

        $all_data = $model->where("deleted_at", null)->select($model->getKeyName())->get();      
        // $all_data = $model->get('target_friend_id');   
        
        //idを取り出す
        $count = $all_data->count();

        $id_list = [];
        for ($i = 0; $i < $count; $i ++) {
            array_push($id_list, $all_data[$i]->getKey());
        }

        return $id_list;
    }

    //条件一つで絞り込み、レコードを返します。
    public function getDataBy($model)
    {
        try {

            $data = Request::all();
            Log::debug("key name : ".$model->getKeyName());
            $data_list = $model->where("deleted_at", null)
            ->where($data['column'], $data['value'])->get();      
            
            // //idを取り出す
        
            return $data_list;

        } catch (\Exception $e) {
            Log::debug($e);
            Log::debug("エラー発生：予期せぬエラーが起きました。");
            ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
        } 
    }

    //レコードを追加します。
    //重複判定は各種モデルに設定された$no_duplicationを使います。
    public function create($model, $columns_ForFirstOrCreate = [])
    {
        try {

            Log::debug(11111);
            $data =  Request::all();
            if (!empty($data['model_name'])) {
                unset($data['model_name']);
            }

            $input_key_list = array_keys($data);
            foreach ($input_key_list as $key) {
                if (gettype($data[$key]) == 'array') {
                    Log::debug(3333);
                    $data[$key] = json_encode($data[$key],JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
            }

            $query = $model->query();

            // $model->fill($data)->save();
            // return $model;

            // 重複確認
            foreach ( array_keys($columns_ForFirstOrCreate) as $column) {
                $query->where($column, $data[$column]);
            }

            Log::debug(4444);
            $check =  count($query->get()->toArray());
            if ($check === 0) {
                Log::debug(5555);
                $model->fill($data)->save();
                return $model;
            }

            Log::debug(77777);
            Log::debug("データが重複しているため、レコード作成失敗です");
            ErrorService::setMessage('・データが重複しているため、レコード作成失敗です');
            return null;

        } catch (\Exception $e) {
            Log::debug($e);
            Log::debug("エラー発生：予期せぬエラーが起きました。");
            ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
            return;
        } 
    }





    //レコード意見を条件で絞り込みレコードを返します。
    public function show($model)
    {
        
    try {
        $data =  Request::all();
        $show_data = array();

        if (!empty($data['model_name'])) {
                unset($data['model_name']);//saveするとき不要なので消します。
        }

            if (!empty($data['select_columns'])) {
                $select_columns = $data['select_columns'];
                unset($data['select_columns']);//saveするとき不要なので消します。

                // echo(json_encode($data));
                $show_data = $model->where($data)->select($select_columns)->first();

            } else {
                $show_data= $model->where($data)->first();
            }
            return $show_data;


        } catch (\Exception $e) {
            Log::debug($e);
            Log::debug("エラー発生：予期せぬエラーが起きました。");
            ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
        } 
    }


    //レコード更新を行います。。
    public function edit($model)
    {

        try {
            $data =  Request::all();
            $edit_data = array();
            $pk = $model->getKeyName();
            
            if (!empty($data['select_columns'])) {
                $select_columns = $data['select_columns'];
                unset($data['select_columns']);

                $edit_data = $model->find($data[$pk]);
                $edit_data->fill($data)->save();

                $edit_data = $model->where($pk, $data[$pk])->select($select_columns)->first();
        
                
            } else {

                
                $edit_data = $model->find($data[$pk]);
                Log::debug(json_encode($edit_data));
                $edit_data->fill($data)->save();
            }
            return $edit_data;

        } catch (\Exception $e) {
            Log::debug($e);
            Log::debug("エラー発生：予期せぬエラーが起きました。");
            ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
        } 
    }

    //レコード削除を行います。
    public function destroy($model)
    {
        try {
      
            $data =  Request::all();
            $pk = $model->getKeyName();
            Log::debug($pk);
            Log::debug(json_encode($data));
            if (!empty($data[$pk])) {
                
                $target_data = $model->where($pk, $data[$pk]);
                if (!empty($target_data)) {
                    $model->destroy($data[$pk]);
                }
                
            } else {
                ErrorService::setMessage('・予期せぬエラー発生');
                return;
            }

        } catch (\Exception $e) {
            Log::debug($e);
            Log::debug("エラー発生：予期せぬエラーが起きました。");
            ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
        } 
        
    }
}
