<?php  
namespace Thilagaraja\Laravelcurdmvc;

use Illuminate\Support\ServiceProvider;
   
class LaravelCurdMvcProvider extends ServiceProvider {
    
        public function boot()
        {
            $this->loadRoutesFrom(__DIR__.'/routes/web.php');
           // include __DIR__.'/routes/web.php';
        }
        public function register()
        {
          //register my controller 
          $this->app->make('Thilagaraja\Laravelcurdmvc\app\Http\Controllers\MasterController');

          $this->app->make('Thilagaraja\Laravelcurdmvc\app\models\User');
        }
}



?>