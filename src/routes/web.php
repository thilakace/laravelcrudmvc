<?php
    
    Route::group(['namespace' => 'Thilagaraja\Laravelcurdmvc\app\Http\Controllers'], function()
{
    Route::get('master/{module}', ['uses' => 'MasterController@index']);
})
?>