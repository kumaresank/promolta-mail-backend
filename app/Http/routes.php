<?php

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'api'], function()
{
    Route::post('authenticate', 'AuthenticateController@authenticate');
    Route::post('compose', 'MailController@compose');
    Route::post('forward', 'MailController@forward');
    Route::post('reply', 'MailController@reply');
    Route::get('mail/read/{id}', 'MailController@makeRead');
    Route::get('mail/count', 'MailController@mailCounts');
    Route::get('inbox/{id?}', 'MailController@inbox');
    Route::get('sent/{id?}', 'MailController@sent');
    Route::get('draft/{id?}', 'MailController@draft');
    Route::get('trash/{id?}', 'MailController@trash');
});
