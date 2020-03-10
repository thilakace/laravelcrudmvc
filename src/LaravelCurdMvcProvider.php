<?php  
namespace Thilagaraja\Laravelcurdmvc;

use Illuminate\Support\ServiceProvider;
   
class LaravelCurdMvcProvider extends ServiceProvider {
    
        public function boot()
        {
            $this->loadRoutesFrom(__DIR__.'/routes/web.php');
            
            $this->loadViewsFrom(__DIR__.'/resources/views', 'mvc');

            $this->publishes([
                __DIR__.'/resources/views' => base_path('resources/views/vendor/laravel-curd-mvc'),
//                __DIR__.'/public/css/' => public_path('resources/assets/css/vendor/laravel-curd-mvc'),
            ], 'assets');
        }
        public function register()
        {
          //register my controller 
          $this->app->make('Thilagaraja\Laravelcurdmvc\app\Http\Controllers\MasterController');

          $this->app->make('Thilagaraja\Laravelcurdmvc\app\models\User');
        }
}



?>