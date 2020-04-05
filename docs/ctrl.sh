#!/bin/sh

php artisan make:controller /Actions/ApiRequest/CheckApi --invokable ;
php artisan make:controller /Actions/ApiRequest/RestApi --resource --model=Tw_Api_Request ;
php artisan make:controller /Actions/AutoFunction/AutoFavortite/StartAutoFavoriteBySnsAccount --invokable ;
php artisan make:controller /Actions/AutoFunction/AutoFavortite/StopAutFavoritBySnsAccount --invokable ;
php artisan make:controller /Actions/AutoFunction/AutoFollow/StartAutoFollowBySnsAccount --invokable ;
php artisan make:controller /Actions/AutoFunction/AutoFollow/StopAutoFollowBySnsAccount --invokable ;
php artisan make:controller /Actions/AutoFunction/AutoFollow/StartAutoUnfollowByAnsAccount --invokable ;
php artisan make:controller /Actions/AutoFunction/AutoFollow/StopAutoUnfollowBySnsAccount --invokable ;
php artisan make:controller /Actions/AutoFunction/AutoTweet/StartAutoTweetBySnsAccount --invokable ;
php artisan make:controller /Actions/AutoFunction/AutoTweet/StopAutoTweetBySnsAccount --invokable ;
php artisan make:controller /Actions/KeyPattern/RestKeyPattern --resource --model=Key_Pattern ;
php artisan make:controller /Actions/KeyPattern/ChooseKeyPatternForFavorite --invokable ;
php artisan make:controller /Actions/KeyPattern/ChooseKeyPatternForFollow --invokable ;
php artisan make:controller /Actions/KeyPattern/removeKeyPatternForFavorite --invokable ;
php artisan make:controller /Actions/KeyPattern/RemoveKeyPatternForFollow --invokable ;
php artisan make:controller /Actions/SnsAccount/RestSnsAccount --resource --model=Tw_Account ;
php artisan make:controller /Actions/TargetAccount/RestTargetAccounts --resource --model=Tw_Target_Account ;
php artisan make:controller /Actions/Tweet/RestTweet --resource --model=Tw_Tweet ;


php artisan make:controller /Actions/ApiRequest/getRequestToken --invokable ;



php artisan make:controller /Actions/DirectLink/TargetList --invokable;
php artisan make:controller /Actions/DirectLink/Keyword --invokable;



<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// use App\Http\Controllers\Controller;
// use App\Domain\Services\ApiService\Tw\RequestToken;
// use App\Domain\Services\InitalActionService;
class KeywordController extends Controller
{
    // use InitalActionService;

    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        return;
        // return InitalActionService::initialAction($req, $req_token);
    }
}
