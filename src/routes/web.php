<?php
Route::group(['namespace' => 'Thomas\Bundle\Http\Controllers'], function () {
    Route::get('thomas', 'HomeController@login');

    Route::group(['prefix'=>'api/v1'],function (){
       Route::post('register','AuthController@register')->name('thomas.auth.register');
       Route::post('login','AuthController@login')->name('thomas.auth.login');
    });
});

