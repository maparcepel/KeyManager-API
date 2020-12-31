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


Route::get('/test', function () {
    return 'Todo OK';
});






Route::group(['middleware' => ['cors']], function () {
    //Rutas a las que se permitirá acceso
    Route::post('/login', 'UserController@login');
    Route::post('/user/register', 'UserController@register');
    Route::put('/user/update', 'UserController@update');
    Route::post('/user/cancelRequest', 'UserController@CancelRequest');
    Route::get('/user/getAllUsersCancelRequest', 'UserController@getAllUsersCancelRequest');
    Route::post('/user/cancel', 'UserController@cancel');
    Route::get('/user/getAllAvailableUsersForLocation/{location_id}', 'UserController@getAllAvailableUsersForLocation');
    Route::get('/user/getUserById/{user_id}', 'UserController@getUserById');
    Route::get('/getAllCustomersAndUsers', 'UserController@getAllCustomersAndUsers');
    Route::post('/user/checkToken', 'UserController@checkToken');
    Route::post('/user/passwordReset', 'UserController@passwordReset');


    Route::get('/getAllCustomersAndLocations', 'LocationController@getAllCustomersAndLocations');
    Route::get('/location/getLocationById/{location_id}', 'LocationController@getLocationById');
    Route::put('/location/update', 'LocationController@update');
    Route::post('/location/register', 'LocationController@register');
    Route::post('/location/cancelRequest', 'LocationController@cancelRequest');
    Route::get('/location/getAllLocationsCancelRequest', 'LocationController@getAllLocationsCancelRequest');
    Route::post('/location/cancel', 'LocationController@cancel');

   
    Route::get('/getLocationEmployees/{location_id}', 'EmployeeController@getLocationEmployees');
    Route::get('/getAllPositionTypes', 'PositionTypeController@getAllPositionTypes');
    Route::post('/employee/register', 'EmployeeController@register');
    Route::post('/employee/update', 'EmployeeController@update');
    Route::get('/employee/getEmployeeById/{employee_id}', 'EmployeeController@getEmployeeById');
    Route::post('/employee/cancelRequest', 'EmployeeController@cancelRequest');
    Route::get('/employee/getAllEmployeesCancelRequest', 'EmployeeController@getAllEmployeesCancelRequest');
    Route::post('/employee/cancel', 'EmployeeController@cancel');

    Route::get('/customer/getCustomerById/{customer_id}', 'CustomerController@getCustomerById');
    Route::post('/customer/cancelRequest', 'CustomerController@cancelRequest');
    Route::get('/customer/getAllCustomersCancelRequest', 'CustomerController@getAllCustomersCancelRequest');
    Route::post('/customer/cancel', 'CustomerController@cancel');
    Route::post('/customer/register', 'CustomerController@register');
    Route::put('/customer/update', 'CustomerController@update');


    Route::get('/zipCode/getDataByZipCode/{zip_code}', 'ZipCodeController@getDataByZipCode');


    //Route::post('/passrecovery', 'PassRecoveryController@getMail');
    //Route::get('/passrecovery2', 'PassRecoveryController@tokenMail');
    
});


