<?php
namespace App\Domain\Services\FollowService;
// use Illuminate\Support\Facades\Request;
// use App\Tw_Account;
// use Illuminate\Support\Facades\DB;
// use App\Jobs\RestartApiJob;
// use App\Facades\ErrorService;
// use Log;
// use App\Domain\Services\SuspendChecker;

use App\Domain\Services\AutoFunctionInterface;
use App\Domain\Services\AutoFunctionRestart;
/**
 * TODO Auto-generated comment.
 */
class FollowRestart extends AutoFunctionRestart implements AutoFunctionInterface{

	public function __invoke() {
		return parent::__invoke();
	}
}
