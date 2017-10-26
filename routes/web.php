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

Route::get('/test',function(){
    dd(pathinfo('http://imgsrc.baidu.com/image/c0%3Dshijue1%2C0%2C0%2C294%2C40/sign=971de28cc1ef7609280691dc46b4c9b9/4a36acaf2edda3cce7305e310be93901203f92cf.jpg'));
});
Route::get('/','ChatController@enter');
Route::get('/cache','ChatController@getCache');
