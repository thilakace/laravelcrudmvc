<?php
    
    Route::group(['namespace' => 'Thilagaraja\Laravelcurdmvc\app\Http\Controllers'], function()
{
    Route::post('master', ['uses' => 'MasterController@index']);
})
?>