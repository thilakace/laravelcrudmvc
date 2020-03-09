<?php  namespace vendor\thilagaraja\LaravelCurdMvc;

use Illuminate\Support\ServiceProvider;
   
class laravelcurdmvcprovider extends ServiceProvider {
        public function boot()
        {
            $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        }
        public function register()
        {

        }
}



?>