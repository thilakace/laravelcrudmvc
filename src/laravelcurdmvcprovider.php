<?php  namespace vendor\thilagaraja\laravelcurdmvc;

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