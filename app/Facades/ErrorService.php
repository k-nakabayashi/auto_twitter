<?php
namespace App\Facades;
use Illuminate\Support\Facades\Facade;

class ErrorService extends Facade
{
  protected static function getFacadeAccessor()
  {
    return 'ErrorService';
  }
}