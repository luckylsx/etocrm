<?php

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

Route::get('/', function () {
    return view('welcome');
});
Route::any("test","HomeController@test");
Route::group(['prefix' => 'api'], function () {
    //测试路由
    Route::any("test","HomeController@test");
    //获得授权路由
    Route::any('sub',"HomeController@getAuthorize");
    //获取code路由
    Route::any('getCode',"HomeController@getCode");
});
