<?php
namespace App\Facades;
use Illuminate\Support\Facades\Facade;

class AutoFunction extends Facade
{
  protected static function getFacadeAccessor()
  {
    return 'AutoFunction';
  }
}