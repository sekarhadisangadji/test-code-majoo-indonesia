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

Route::group(['prefix' => 'v1'], function() {
    
    Route::group(['prefix' => 'auth'], function() {
        
        Route::post('login','AuthController@login');

    });

    Route::group(['prefix' => 'report', 'middleware' => 'authjwt'], function() {
        
        Route::post('merchant/{merchant_id}/omzet','ReportController@reportMerchantOmzet');

        Route::post('merchant/{merchant_id}/outlet/{outlet_id}/omzet','ReportController@reportMerchantOutletOmzet');

    });

});


