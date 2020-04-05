<?php
//凍結からの復帰

namespace App\Domain\Services\UnfollowService;
use App\Domain\Services\SuspendChecker;

use App\Domain\Services\AutoFunctionInterface;
use App\Domain\Services\AutoFunctionRelease;
/**
 * TODO Auto-generated comment.
 */
class UnfollowRelease extends AutoFunctionRelease implements AutoFunctionInterface {
	use SuspendChecker;

	public function __invoke() {
		return parent::__invoke();
	}
}
