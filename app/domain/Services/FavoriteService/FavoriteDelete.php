<?php
//凍結からの復帰

namespace App\Domain\Services\FavoriteService;
use App\Domain\Services\AutoFunctionInterface;
use App\Domain\Services\AutoFunctionDelete;
/**
 * TODO Auto-generated comment.
 */
class FavoriteDelete extends AutoFunctionDelete implements AutoFunctionInterface {

	public function __invoke() {
		return parent::__invoke();
	}
}
