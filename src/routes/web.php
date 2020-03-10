<?php
    
    Route::group(['namespace' => 'Thilagaraja\Laravelcurdmvc\app\Http\Controllers'], function()
{
    Route::get('contact', ['uses' => 'MasterController@index']);
})
?>