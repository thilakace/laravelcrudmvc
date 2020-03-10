<?php
    
    Route::group(['namespace' => 'Thilagaraja\Laravelcurdmvc\app\Http\Controllers'], function()
{
    Route::get('master', ['uses' => 'MasterController@index']);
})
?>