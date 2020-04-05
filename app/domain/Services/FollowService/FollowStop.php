<?php
namespace App\Domain\Services\FollowService;
use App\Domain\Services\AutoFunctionInterface;
use App\Domain\Services\AutoFunctionStop;

/**
 * TODO Auto-generated comment.
 */
class FollowStop extends AutoFunctionStop implements AutoFunctionInterface{
	public function __invoke() {
		return parent::__invoke();
	}
}
