<?php
namespace App\Domain\Services;
use App\Tw_Target_Friend;
use Illuminate\Support\Facades\DB;
use App\Facades\ErrorService;
use Log;
/**
 * TODO Auto-generated comment.
 */
trait InsertSnsAccount {

  //model：ターゲットアカウント
  //model2 :自分のtwitterアカウント
  public static function prepareInsert ($model, $model2, $tw_account_FromTw, $keyword) {

  
    $obj_list = [];
		foreach ($tw_account_FromTw->users as $obj) {

			if ($obj->protected == true) {
				// Log::debug("・$obj->name さんは非公開アカウントのため、ターゲット対象にできません。");
				continue;
			}

			// var_dump($obj->name);
			// continue;
			//日本語判定
			if (!preg_match('/[一-龠]+|[ぁ-ん]+|[ァ-ヴー]+|[ａ-ｚＡ-Ｚ０-９]+/u',$obj->description)) {
				// var_dump("$obj->name : プロフィールに日本語が使われていないため、登録拒否します。");
				continue;
      }
      
      // // 部分一致判定
			$result_count = self::searchKeyword($obj->description, $keyword);

			if ($result_count <= 0) {
				// Log::debug("$obj->name : ターゲットフレンドの対象外です");
				continue;
			}

			array_push(
				$obj_list,
				[
					'target_friend_user_id' => $obj->id,
					'name' => $obj->name,
					'screen_name' => $obj->screen_name,
					'followers_count' => $obj->followers_count,
					'friends_count' => $obj->friends_count,
          'description' => $obj->description,
          'profile_image_url_https' => $obj->profile_image_url_https,
					"app_id" => $model2->app_id,
          "key_pattern_id" => $model2->key_pattern_id,
					"target_account_id" => $model->target_account_id,
					'tw_account_id' => $model2->getKey(),
					'created_at' => date("Y/m/d H:i:s"),
					'updated_at' => date("Y/m/d H:i:s"),
				]
      );
    }
    
    return $obj_list;
  }

  // target_friend_user_idが重複をしている場合、保存しない
  public static function insertFriendRecord ($tw_target_account_model, $tw_target_friend_list, $tw_target_friend_model, $my_tw_account) {

    //アプリアカウントごとのtarget_friend_user_idを取り出す
    $target_friend_user_id_list = Tw_Target_Friend::select('target_friend_user_id')->where('tw_account_id', $my_tw_account->getKey())->get()->toArray();
    $user_id_list_from_db = [];
    // var_dump("fro Table");
    foreach ($target_friend_user_id_list as $item) {
      array_push($user_id_list_from_db, $item['target_friend_user_id']);
      // var_dump($item['target_friend_user_id']);
    }

    //引数から渡ってきたtarget_friend_user_idを取り出す
    // var_dump("fro tw");
    $user_id_list_from_tw = [];
    foreach ($tw_target_friend_list as $item) {
    
      array_push($user_id_list_from_tw, $item['target_friend_user_id']);
      // var_dump($item['target_friend_user_id']);
    }

    //TwitterAPIから取得したIDリストから、DBのと重複しているuser_idを除外
    // var_dump("diff");
    $choosen_user_id_list = array_diff($user_id_list_from_tw, $user_id_list_from_db);
    // var_dump($choosen_user_id_list);

    //必要なレコードだけinsert
    // var_dump($tw_target_friend_list);
    $counter = 0;

    //tw_target_friend_list：登録するデータ
		//choosen_user_id_list：登録する対象を絞り込むリスト
		$id_list_inserted = [];
    foreach ($tw_target_friend_list as $tw_target_friend) {
      foreach ($choosen_user_id_list as $item) {
        if ($tw_target_friend['target_friend_user_id'] == $item) {
          $counter ++;
					$id = DB::table($tw_target_friend_model->getTable())->insertGetId($tw_target_friend);
					array_push($id_list_inserted, $id);
          break;
        } 
      }
    }
		Log::debug("ターゲットフレンドを $counter 件　追加しました。");
		return $id_list_inserted;
  }


	//$keyword : string
	//return Str
	public static function searchKeyword ($target ,$pares) {
		// Log::debug($target);
		try {

		//AND OR NOT　検索実行
		$option_list = [];
		foreach ($pares as $item) {
		
			array_push($option_list, $item->opt);
		}

		$result_count = 0;
		foreach ($pares as $item) {
		// for ($i = 0; $i < count($pares); $i ++) {

			if ($item->txt === null) {
				continue;
			}
			// var_dump("result : ".preg_match('/.*'.$item->txt.'.*/', $target));

			if ($item->opt === "and") {
				// var_dump("judge");

				if (preg_match('/.*'.$item->txt.'.*/', $target) == 0) {

					if (count($option_list) === 1) {
						$result_count = 0;
						break;
					} 

					$result_count = 0;
					break;
				};

				$result_count ++;
				continue;

			} else if ($item->opt === "not") {
		
				if (preg_match('/.*'.$item->txt.'.*/', $target) == 1) {

					if (count($option_list) === 1) {
						$result_count = 0;
						break;
					} 
					
					$result_count = 0;
					break;
				};

				$result_count ++;
				continue;

			} else if ($item->opt === "or") {

			if (preg_match('/.*'.$item->txt.'.*/', $target) == 0) {

					if (count($option_list) === 1) {
						$result_count = 0;
						break;
					} 
					continue;
				};

				$result_count ++;
				continue;
			}  
		}
		
			//０なら対象外

			return $result_count;

		} catch (\Exception $e) {
			var_dump($e);
			var_dump("エラー発生：予期せぬエラーが起きました。");

		}
	}

}
