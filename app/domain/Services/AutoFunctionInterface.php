<?php
//自動機能の呼び出し元です
namespace App\Domain\Services;

interface AutoFunctionInterface {

  public function __invoke();
}