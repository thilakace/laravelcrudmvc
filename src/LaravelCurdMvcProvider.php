<?php  namespace Thilagaraja\Laravelcurdmvc;

use Illuminate\Support\ServiceProvider;
   
class LaravelCurdMvcProvider extends ServiceProvider {
    
        public function boot()
        {
            $this->loadRoutesFrom(__DIR__.'/routes/web.php');
           
        }
        public function register()
        {

        }
}



?>