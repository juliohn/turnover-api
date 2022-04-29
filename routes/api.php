

<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Login Via JWT TYMON
Route::group(array('prefix' => 'auth'), function () {
    Route::post('logar', 'Api\AuthController@login');
});

// User
Route::resource('user', 'Api\UserController');

//- Autenticate Routes
Route::group(array('middleware' => array('apiJwt')), function () {

    // Balance
    Route::get('balance', 'Api\BalanceController@index');

    // Expense
    Route::resource('expense', 'Api\ExpenseController');
    // Check
    Route::resource('check', 'Api\CheckController');

    Route::get('check-incomes', 'Api\CheckController@incomes');
});
