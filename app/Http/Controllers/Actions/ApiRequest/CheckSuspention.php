<?php

namespace App\Http\Controllers\Actions\ApiRequest;

use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Controller;
use App\Facades\ErrorService;
use Log;
use App\Domain\Services\SuspendChecker;

class CheckSuspention extends Controller
{
    use SuspendChecker;
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {

        
        try {
            Log::debug('check suspention');
            $id = Request::input('tw_account_id');
            
            //凍結確認
            if ($this->start($id, false)) {
                return response()->json(['errors' => ErrorService::getMessage()]);
            }
            


        } catch (\Exception $e) {
            var_dump($e);
            ErrorService::setMessage('エラー発生：予期せぬエラーが起きました。');
          
        } finally{
            return response()->json(['errors' => ErrorService::getMessage()]);
        }
    }
}
