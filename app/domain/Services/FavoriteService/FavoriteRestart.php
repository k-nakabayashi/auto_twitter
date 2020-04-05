<?php
namespace App\Domain\Services\FavoriteService;
use App\Domain\Services\AutoFunctionInterface;
use App\Domain\Services\AutoFunctionRestart;
/**
 * TODO Auto-generated comment.
 */
class FavoriteRestart  extends AutoFunctionRestart implements AutoFunctionInterface{
	/**
	 * TODO Auto-generated comment.
	 * $model : tw_accout
	 */
	public function __invoke() {
		return parent::__invoke();
	}
}
