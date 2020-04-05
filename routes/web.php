<?php
// use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Route::group(['middleware' => 'auth.very_basic'], function() {

    Route::get('/', function () {
        return view('welcome');
    });//->middleware('verified');//->middleware('auth.basic');
    // Auth::routes();
    Auth::routes(['verify' => true]); 

Route::middleware('verified')->group(function () {
 
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/getTwAccount', 'ApiAuthController@index');

    //REST系
    Route::resource('/RestApi', 'Actions\ApiRequest\RestApi');

    //自動機能 フォロー　アンフォロー　いいね
    Route::post('/startAutoFunction', 'AutoFunctionController@start');//vali ok
    Route::post('/stopAutoFunction', 'AutoFunctionController@edit');//vali ok
    Route::post('/restartAutoFunction', 'AutoFunctionController@edit');//vali ok
    Route::post('/deleteAutoFunction', 'AutoFunctionController@edit');//vali ok
    
    Route::post('/releaseAutoFunction', 'AutoFunctionController@release');//vali ok


    //自動　ツイート予約
    Route::get('/startTweet', 'Actions\AutoFunction\TweetStartAction');

    //アカウント
    Route::post('/createTarget', 'Actions\SnsAccount\CreateTarget');////vali ok, 機能ok
    Route::post('/GetRequestToken', 'Actions\ApiRequest\GetRequestToken');

    //アカウント凍結確認
    Route::post('/checkSuspention', 'Actions\ApiRequest\CheckAccountSuspention');

    //ターゲットフォロワー
    Route::get('/getFollowerTarget', 'TargetFollowerController@follower_target');
    Route::get('/getUnFollow', 'TargetFollowerController@unfollow');
    Route::get('/getFollow', 'TargetFollowerController@follow');
    Route::get('/getFollower', 'TargetFollowerController@follower');

    //ダイレクトなリクエストに対応
    Route::get('/target_list', 'Actions\DirectLink\TargetList');
    Route::get('/keyword', 'Actions\DirectLink\Keyword');
    Route::get('/favorite_keyword', 'Actions\DirectLink\FavoriteKeyword');
    Route::get('/my_tw_account', 'Actions\DirectLink\MyTweetAccount');
    Route::get('/my_tw_account{id}', 'Actions\DirectLink\MyTweetAccount');
    Route::get('/how_to_use', 'Actions\DirectLink\HowToUse');
    
});
    // Route::get('auth/twitter', 'Auth\SocialAuthController@redirectToProvider');
    // Route::get('auth/twitter/callback', 'Auth\SocialAuthController@handleProviderCallback');
    // Route::get('auth/twitter/logout', 'Auth\SocialAuthController@logout');
    

