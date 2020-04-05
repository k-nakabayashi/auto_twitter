<?php
//エラーメッセージです。
//$messageをVueに返すと、Vue側で内容を「アラート」します。
namespace App\Domain\Services;

class ErrorService {

  public $message = [];

  public function __construct()
  {

  }
  public function getMessage()
  {
    return $this->message;
  }
  public function setMessage($message)
  {
    array_push($this->message, $message);
  }
}