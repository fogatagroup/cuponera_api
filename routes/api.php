<?php

//Usuarios Begin Enpoint
Route::post('/login', 'AuthController@login')->name('login');
//Route::post('/users', 'AuthController@users');
Route::get('/alluser', 'AuthController@allusers');
Route::post('/users/new', 'AuthController@storeNewUser');
Route::get('/users/{id}', 'AuthController@getUserPerId');
Route::post('/users/update/{id}', 'AuthController@updateUserPerId');
Route::post('/users/delete/{id}', 'AuthController@deleteUserPerId');
//Route::get('/users/login/[{user_name}&{password}]', 'AuthController@loginUrlMethodGet');
//End Usuarios Endpoint

//offer
Route::get('/alloffer', 'OfferController@getAllOfferInDb');
Route::post('/offer/new', 'OfferController@newOfferStore');
Route::get('/offer', 'OfferController@getOfferPerCode');
Route::get('/offer/code/{id}', 'OfferController@getOfferPerId');
Route::post('/offer/update/{id}', 'OfferController@updateOfferPerId');
Route::post('/offer/delete/{id}', 'OfferController@deleteOfferPerId');
//Route::post('/users/login/[{user_name}&{password}]', 'OfferController@login_url');

//Coupon Begin Endpoint
Route::get('/allcoupon', 'CouponController@getAllCoupon');
Route::get('/coupon/{id}', 'CouponController@getCouponPerId');
Route::get('/coupon/customers/{id_customers}', 'CouponController@getCouponPerIdCustomers');
Route::get('/coupon/offer/{id_offer}', 'CouponController@getOfferPerIdOffer');
Route::post('/coupon/exchange', 'CouponController@exchangeCoupon');
Route::get('/coupon/exchange/customers/{id_customers}', 'CouponController@getAllExchangeCouponFilterByCustomers');
//End Coupon Endpoint

//Customers Begin Endpoint
Route::get('/allcustomers', 'CustomerController@getAllCustomers');
Route::get('/customers/capture/{id}', 'CustomerController@getCustomersCaptureById');
Route::post('/customers/new', 'CustomerController@storeNewCustomers');
Route::put('/customers/update/{id}', 'CustomerController@updateCustomersById');
Route::delete('/customers/delete/{id}', 'CustomerController@deleteCustomersById');
//End Customers Endpoint

//UserType Begin Endpoint
Route::get('/allusertype', 'UserTypeController@getAllUserType');
Route::get('/usertype/{id}', 'UserTypeController@getUserTypeById');
Route::post('/usertype/new', 'UserTypeController@storeNewUserType');
Route::put('/usertype/update/{id}', 'UserTypeController@updateUserTypeById');
Route::delete('/usertype/delete/{id}', 'UserTypeController@deleteUserTypeById');
//End UserType Endpoint

//Sales Begin Endpoint
Route::get('/allsales', 'SalesController@getAllSalesInDb');
Route::get('/sales/{id}', 'SalesController@getSalesById');
Route::post('/sales/new', 'SalesController@storeNewSales');
Route::put('/sales/update/{id}', 'SalesController@updateSalesById');
Route::delete('/sales/delete/{id}', 'SalesController@deleteSalesById');
//End Sales Endpoint

//Company Begin Endpoint
Route::get('/allcompanies', 'CompanyController@getAllCompanyInDb');
//End Company Begin Endpoint

//Transaction Begin Endpoint
Route::get('/alltransaction', 'TransactionController@getAllTransactionInDb');
//End Transaction Endpoint


