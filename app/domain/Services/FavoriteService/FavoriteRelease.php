<?php
//凍結からの復帰

namespace App\Domain\Services\FavoriteService;
use App\Domain\Services\SuspendChecker;

use App\Domain\Services\AutoFunctionInterface;
use App\Domain\Services\AutoFunctionRelease;
/**
 * TODO Auto-generated comment.
 */
class FavoriteRelease extends AutoFunctionRelease implements AutoFunctionInterface {
	use SuspendChecker;

	public function __invoke() {
		return parent::__invoke();
	}
}
