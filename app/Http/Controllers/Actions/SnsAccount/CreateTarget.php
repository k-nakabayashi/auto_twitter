<?php
//ターゲットアカウントを登録します。
namespace App\Http\Controllers\Actions\SnsAccount;
use App\Domain\Subsystem\AdminSnsAccount\CreateTargetAccount;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Factory;
use App\Facades\ErrorService;
use Log;
class CreateTarget extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(
        CreateTargetAccount $domain, 
        Request $request, 
        Factory $factory
        )
    {   

        //追加したレコードを返す
        $validator = $factory->make($request->all(), ['screen_name' => 'required']);
       
        if ($validator->fails()) {

            ErrorService::setMessage('・「Twitterアカウントとスクリーンネーム」の選択が必要です。');
            return response()->json(['errors' => ErrorService::getMessage()]);
        }

        $model = $domain->__invoke();
        Log::debug(ErrorService::getMessage());
        return response()->json(['data' => $model, 'errors' => ErrorService::getMessage() ]);
    }
}
