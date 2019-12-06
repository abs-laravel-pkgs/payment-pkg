<?php
Route::group(['namespace' => 'Abs\PaymentPkg\Api', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'Payment-pkg/api'], function () {
		Route::group(['middleware' => ['auth:api']], function () {
			// Route::get('taxes/get', 'TaxController@getTaxes');
		});
	});
});