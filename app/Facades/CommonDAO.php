<?php
namespace App\Facades;
use Illuminate\Support\Facades\Facade;

class CommonDAO extends Facade
{
  protected static function getFacadeAccessor()
  {
    return 'CommonDAO';
  }
}