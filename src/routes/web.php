<?php
Route::group(['namespace' => 'Thomas\Bundle\Http\Controllers'], function () {
    Route::get('thomas', 'HomeController@login');
});

