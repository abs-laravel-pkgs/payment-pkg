<?php

Route::group(['namespace' => 'Abs\PaymentPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'Payment-pkg'], function () {
	Route::get('/Payments/get-list', 'PaymentController@getPaymentList')->name('getPaymentList');
	Route::get('/Payment/get-form-data/{id?}', 'PaymentController@getPaymentFormData')->name('getPaymentFormData');
	Route::post('/Payment/save', 'PaymentController@savePayment')->name('savePayment');
	Route::get('/Payment/delete/{id}', 'PaymentController@deletePayment')->name('deletePayment');

});