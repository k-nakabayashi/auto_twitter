<?php
namespace App\Facades;
use Illuminate\Support\Facades\Facade;

class QueuePid extends Facade
{
  protected static function getFacadeAccessor()
  {
    return 'QueuePid';
  }
}