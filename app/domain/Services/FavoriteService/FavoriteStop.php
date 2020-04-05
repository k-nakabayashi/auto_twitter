<?php
namespace App\Domain\Services\FavoriteService;
use App\Domain\Services\AutoFunctionInterface;
use App\Domain\Services\AutoFunctionStop;

/**
 * TODO Auto-generated comment.
 */
class FavoriteStop extends AutoFunctionStop implements AutoFunctionInterface{
	
	public function __invoke() {
		return parent::__invoke();
	}
}
