<?php namespace Thilagaraja\Laravelcurdmvc\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Thilagaraja\Laravelcurdmvc\app\models\User;
use Request;
use Illuminate\Support\Str;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class MasterController extends Controller
{
    public function index()
    {
        $response = NULL;
        if(Request::exists('module')){
            $module = $this->getSlug(Request::get('module'));
            $exist =  $this->moduleExist($module);
            if($exist == 'NotExist'){
                $name_class = str_replace(" ", "_", $module);
                $cntrl_name = ucfirst($name_class);
                $table_name = $name_class;
                if (!Schema::hasTable($table_name)) {
                    $response =  $this->create($module);
                }else{
                    $response = "Table already available in the DB.";
                }

                
            }else{
                $response = $exist;
            }
           
        }else{
            $response = "Module not found";
        }
       
        return $response;
        
    }
    public function getSlug($str){
        return Str::slug($str, "_");
    }

    

    public function moduleExist($module){
        
        $result = '';
        if (!Schema::hasTable('crud_master')) {
            // Code to create table
            Schema::create("crud_master", function (Blueprint $table) {
                
                $table->increments("id");
                $table->string("module");
                $table->string("slug");
                $table->integer("status",false,true)->default(1);
                $table->timestamps();

            });

            $values = array('module' => $module,'slug' => $module);
            DB::table('crud_master')->insert($values);
            
            $this->createCrudMasterModel('crud_master'); // create crud master model
            $this->createAvatar('avatar'); // create image table and model
            $result = 'NotExist';
        }else{
            $exist = DB::table('crud_master')->where('module','=', $module)->count();
            if($exist != 0){
                $result = 'Exist';
            }else{
                $result = 'NotExist';
            }
        }

        return $result;

    }

    public function create_migration_content(){
        $migrationContent = '';
        $migrationContent .= '$table->increments("id");'.PHP_EOL;
        if(Request::exists('fields')){
            foreach (Request::get('fields') as $key => $value) {
                # code...
                $column = Str::slug($value["column"], "_");
                $nullable = (isset($value["required"]) && $value["required"] == true) ? false : true; 
                $unique = (isset($value["unique"]) && $value["unique"] == true) ? true : false; 
                switch ($value['date_type']){
                    
                    case 'string':
                     $string = ' $table->string("'.$column.'")';
                     if($nullable){
                        $string .= '->nullable()';
                     }
                     if($unique){
                        $string .= '->unique()';
                     }
                     $string .= ';';
                     $migrationContent .= $string.PHP_EOL;
                    break;  

                    case 'integer':
                        $integer =   ' $table->integer("'.$column.'")';
                        if($nullable){
                            $integer .= '->nullable()';
                        }
                        if($unique){
                            $integer .= '->unique()';
                         }
                        $integer .= ';';
                        $migrationContent .= $integer.PHP_EOL;

                    break; 

                    case 'date':
                        $date =  ' $table->date("'.$column.'")';
                        if($nullable){
                            $date .= '->nullable()';
                        }
                        $date .= ';';
                        $migrationContent .= $date.PHP_EOL;

                    break;
                    case 'dateTime':
                        $dateTime =  ' $table->dateTime("'.$column.',$precision = 0")';
                        if($nullable){
                            $dateTime .= '->nullable()';
                        }
                        $dateTime .= ';';
                        $migrationContent .= $dateTime.PHP_EOL;

                        
                     break;  
                     case 'bigInteger':
                        $bigInteger =  ' $table->bigInteger("'.$column.'")';
                        if($nullable){
                            $bigInteger .= '->nullable()';
                        }
                        $bigInteger .= ';';
                        $migrationContent .= $bigInteger.PHP_EOL;

                     break;   
                      
                    default:
                    
                } 
            }
            $migrationContent .= ' $table->integer("status",false,true)->default(0);'.PHP_EOL;
            $migrationContent .= ' $table->integer("created_by",false,true);'.PHP_EOL;
            $migrationContent .= ' $table->integer("modified_by",false,true);'.PHP_EOL;
            $migrationContent .= ' $table->timestamps();';
            return $migrationContent;

            
        }
    }

    public function getValidationFields($table_name,$mode){
        $content = 'if($value =="status" || $value =="id" || $value=="created_at" || $value=="updated_at" || $value =="remember_token"){'.PHP_EOL;
        $content .= '}';
        if(Request::exists('fields')){
            foreach (Request::get('fields') as $key => $value) {
                $column = Str::slug($value["column"], "_");
                $nullable = (isset($value["required"]) && $value["required"] == true) ? false : true; 
                $unique = (isset($value["unique"]) && $value["unique"] == true) ? true : false; 
                
                $rules = NULL;
                if(isset($value["required"]) && $value["required"] == true){
                    $rules .= 'required|';
                }

                if(isset($value["column"]) && $value["column"] == 'email'){
                    $rules .= 'email|';
                }

                

                if(isset($value["unique"]) && $value["unique"] == true){
                    $rules .= 'unique:'.$table_name.','.$column;
                    if($mode =='update'){
                        $rules .= ',$id';
                    }
                    $rules .= '|';
                }

                if($rules != NULL){
                        $content .= 'else if($value == "'.$column.'"){'.PHP_EOL;
                        $content .= '$rules[$value] = "'.$rules.'";';
                        $content .= PHP_EOL.'}'.PHP_EOL;
                }

                
                  
                    
            }
        }   
        return  $content;
    }

    public function create($name){
        $name_class = str_replace(" ", "_", $name);
        $cntrl_name = ucfirst($name_class);
        $table_name = $name_class;
      //  return $this->getValidationFields($table_name);
        $this->done($name_class,$cntrl_name,$table_name);
        return response()->json([
            "status" => " ".$name_class." has been create successfully",
            "data" => ''
        ]); 
    }

    public function done($name_class,$cntrl_name,$table_name){
        
        $date = date('Y_m_d');
        $rand = rand(111111,999999);
        $word = $date."_".$rand."_create_".$name_class;

        $mig_name = "Create".ucfirst($name_class)."Table";

        $file_mig = fopen(base_path()."/"."database/migrations/".$word."_table.php","w");

        $migration = 
        '<?php

        use Illuminate\Support\Facades\Schema;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Database\Migrations\Migration;

        class '.$mig_name.' extends Migration
        {
            /**
             * Run the migrations.
             *
             * @return void
             */
            public function up()
            {
                Schema::create("'.$table_name.'", function (Blueprint $table) {
                
                    '.$this->create_migration_content().'

                });
            }

            /**
             * Reverse the migrations.
             *
             * @return void
             */
            public function down()
            {
                Schema::dropIfExists("'.$table_name.'");
            }
        }

        ';
        echo fwrite($file_mig,$migration);
        fclose($file_mig);
        

        $model_folder = base_path()."/"."app/Models";
        if(!is_dir($model_folder)){
          mkdir($model_folder,0777);
        }
        $file_model = fopen($model_folder."/".$name_class.".php","w");

        $model = 
        '<?php

        namespace App\Models;

        use Illuminate\Database\Eloquent\Model;
        use Schema;

        class '.$name_class.' extends Model
        {
            //
            protected $table = "'.$table_name.'";

            
            public static function getTableColumns($use=null, $new_column=null) {
                
                $columns = Schema::getColumnListing("'.$table_name.'");
                if(isset($new_column) && $use=="needed"){
                    return $result=array_intersect($new_column,$columns);
                }else if(isset($new_column) && $use=="except"){
                return $result=array_diff($columns,$new_column);      
                }
                else{
                return $columns;  
                }
                
            }
        }


        ';
        echo fwrite($file_model,$model);
        fclose($file_model);

        $controller_folder = base_path()."/"."app/Http/Controllers/Master";
        if(!is_dir($controller_folder)){
        mkdir($controller_folder,0777);
        }

        $file = fopen($controller_folder."/".$cntrl_name."Controller.php","w");

        $Controller = 
        '<?php

        namespace App\Http\Controllers\Master;

        use App\Http\Controllers\Controller;
        use Illuminate\Http\Request;
        use Input;
        use Session;
        use Illuminate\Support\Facades\Validator;
        use Illuminate\Support\Facades\Redirect;
        use App\Models\\'.$name_class.';
        use App\Models\crud_master;
        use App\Models\Avatar;
        use Auth;
        use Illuminate\Http\Response;



        class '.$cntrl_name.'Controller extends Controller
        {
            /**
             * Create a new controller instance.
             *
             * @return void
             */
            public function __construct()
            {
            // $this->middleware("auth");
            }

            /**
             * Show the application dashboard.
             *
             * @return \Illuminate\Http\Response
             */
            public function index()
            {
                    $data["list"] = '.$name_class.'::where("status","!=",2)->get();
                    $new_column = array("id"); // add as your need
                    $data["table_column"] = '.$name_class.'::getTableColumns("needed",$new_column);
                    $data["form_column"] = '.$name_class.'::getTableColumns();
                    return response()->json([
                        "status" => "Success",
                        "data" => $data
                    ]); 
            }
            
            public function store () {
                
                
                $except = array("status","id","created_at","updated_at","created_by","modified_by");
                $column = '.$name_class.'::getTableColumns("except",$except);
                
                /* For getting input values for validation rules*/
                
                $rules = array();
                
                foreach ($column as $key => $value) {
                    '.$this->getValidationFields($table_name,'save').'
                    
                }

                $validator = Validator::make(Input::all(), $rules);

                if ( $validator->fails() )
                {
                    return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
                }
                    else
                {
                
                    $data  = new '.$name_class.'(); //create object for save
                    
                    foreach ($column as $value) {
                        if($value =="status" ){
                            $data->status = 0;
                        }
                        else if ( $value =="id" || $value=="created_at" || $value=="updated_at" || $value=="created_by" || $value=="modified_by" || $value =="remember_token") {
                        
                        }
                        else if ($value == "image") {
                        
                            $img = Input::file("image");

                            if(isset($img) && count($img) > 0){
                                /* Multiple files upload */
                            
                                $save_image = crud_master::multiFileUpload($img,"'.$name_class.'");

                                $data->image  = $save_image;

                            }
                            
                        
                        }
                        else{
                        $data->$value  = Input::get($value); 
                        
                        }
                    
                    }
                    $data->status = 0;
                    $data->created_by = 0;
                    $data->modified_by = 0;
                    $data->save();
                    
                    return response()->json([
                        "status" => "The Data has been added.",
                        "data" => $data->id
                    ]); 
                }
        
                   
                
            }
            public function edit($id) {
                    
                    $new_column = array("id"); // add as your need
                    
                    $data["table_column"] = '.$name_class.'::getTableColumns("needed",$new_column);
                    
                    $data["form_column"] = '.$name_class.'::getTableColumns();
                    
                   

                    $item_data =  '.$name_class.'::find($id);
                    if(isset($item_data->image)){
                         $item_data->image = Avatar::get_multi_image($item_data->image);
                    }
                    
                    $data["item"] = $item_data;
                    
                    return response()->json([
                        "status" => "Success",
                        "data" => $data
                    ]); 

            }
            
            public function update ($id) {
                    
                 

                $except = array("status","id","created_at","updated_at","created_by","modified_by");
                $column = '.$name_class.'::getTableColumns("except",$except);
                
                /* For getting input values for validation rules*/
                
                $rules = array();
                
                foreach ($column as $key => $value) {
                    '.$this->getValidationFields($table_name,'update').'
                        
                }

                    $validator = Validator::make(Input::all(), $rules);

                    if ( $validator->fails() )
                    {
                        return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
                    }
                    else
                    {
                    
                    $data  = '.$name_class.'::find($id);
                    
                    foreach ($column as $value) {
                            
                            if($value =="status" ){
                                $data->status = 0;
                            }
                            else if ( $value =="id" || $value=="created_at" || $value=="updated_at" || $value =="remember_token") {
                            
                            }
                            else if ($value == "image") {
                            
                                $img = Input::file("image");

                                if(isset($img) && count($img) > 0){
                                    
                                    /* Multiple files upload */
                                
                                    $save_image = crud_master::multiFileUpload($img,"'.$name_class.'",$data->image);

                                    $data->image  = $save_image; 
                                }
                                
                                
                            }
                            else{ 

                            $data->$value  = Input::get($value); 
                            
                            }
                        
                        }

                            $data->save();
                            
                            return response()->json([
                                "status" => "The Data has been updated.",
                                "data" => $data->id
                            ]); 
                    }

                    

            }
            public function delete ($id) {
                    /**
                     * this is delete process
                     */

                    $data  = '.$name_class.'::find($id);
                    $data->status = 2;
                    $data->save();
                    return response()->json([
                        "status" => "The Data has been deleted.",
                        "data" => $data->id
                    ]);  
            }
            public function status ($id,$param) {
                /**
                     * this is status change process
                    */
                $data  = '.$name_class.'::find($id);
                $data->status = $param;
                $data->save();
                
                return response()->json([
                    "status" => "Status has been successfull changed.",
                    "data" => $data->id
                ]);  
            }

            public function delete_img ($id) {
                    /**
                     * this is delete process for item single image
                     * unlink used to remove from folder too
                     */

                    $data  = Avatar::find($id);
                    $data->delete();
                    $image  =  $data->path."/".$data->image;
                    $thumb  =  $data->path."/thumb/".$data->image;
                    unlink($image);
                    unlink($thumb);
                    Session::flash("success", "Image has been successfully deleted");
                    
                    return redirect("'.$name_class.'");
            }
            

        }

        ';
        echo fwrite($file,$Controller);
        fclose($file);



        $file2 = base_path(). "/routes/web.php";

        $routes = file_get_contents($file2);

        $routes .= 
        '
        // below routes for '.ucfirst($name_class).'
        
        Route::get("/webhook/'.$name_class.'", [App\Http\Controllers\Master\\'.$cntrl_name.'Controller::class, "index"]);
        //Route::get("/webhook/'.$name_class.'/create", [App\Http\Controllers\Master\\'.$cntrl_name.'Controller::class, "create"]);
        Route::post("/webhook/'.$name_class.'", [App\Http\Controllers\Master\\'.$cntrl_name.'Controller::class, "store"]);
        Route::get("/webhook/'.$name_class.'/{id}/edit", [App\Http\Controllers\Master\\'.$cntrl_name.'Controller::class, "edit"]);
        Route::put("/webhook/'.$name_class.'/{id}", [App\Http\Controllers\Master\\'.$cntrl_name.'Controller::class, "update"]);
        Route::delete("/webhook/'.$name_class.'/{id}", [App\Http\Controllers\Master\\'.$cntrl_name.'Controller::class, "delete"]);
        //Route::get("/webhook/'.$name_class.'/{id}/delete_img",[App\Http\Controllers\Master\\'.$cntrl_name.'Controller::class, "delete_img"]);
        Route::get("/webhook/'.$name_class.'/{id}/{param}/status", [App\Http\Controllers\Master\\'.$cntrl_name.'Controller::class, "status"]);
        ';

        file_put_contents($file2, $routes);

        
        $values = array('module' => $name_class,'slug' => $name_class);
        DB::table('crud_master')->insert($values);

        
        
        
        

    }

    public function createCrudMasterModel($master ='crud_master'){
        $model_folder = base_path()."/"."app/Models";
        if(!is_dir($model_folder)){
          mkdir($model_folder,0777);
        }
        $file_model = fopen($model_folder."/".$master.".php","w");

        $model = 
        '<?php

        namespace App\Models;

        use Illuminate\Database\Eloquent\Model;
        use Schema;
        use File;
        use Image;
        use App\Models\Avatar;

        class '.$master.' extends Model
        {
            //
            protected $table = "'.$master.'";

            
            public static function getTableColumns($use=null, $new_column=null) {
                
                $columns = Schema::getColumnListing("'.$master.'");
                if(isset($new_column) && $use=="needed"){
                    return $result=array_intersect($new_column,$columns);
                }else if(isset($new_column) && $use=="except"){
                return $result=array_diff($columns,$new_column);      
                }
                else{
                return $columns;  
                }
                
            }
            public static function multiFileUpload($allFiles,$source,$unique_id=null){
    	
                $count = count($allFiles);
                
                if(isset($unique_id)){
                    $unique_id = $unique_id;
                }else{
                      $unique_id = mt_rand(111,9999999);
                }
                
                $i = 0; 
                $invalid = array();
                $valid  = array();
                foreach ($allFiles as $file)
                      {
      
                         if($file->getSize() != false){
                               $originalname = $file->getSize();
                              $year = date("Y");
                              $month = date("m");
                              $day = date("Y_m_d_h_i_s");
                              $destinationPath = "uploads/".$source."/".$year."/".$month;
                              if (!file_exists($destinationPath)) 
                              {
                                  File::makeDirectory($destinationPath, 0777, true);
                              }
      
                              
                                  $extension = $file->getClientOriginalExtension();
                                  $random_number    = mt_rand(1000, 9999);
                                  $alphanumeric_val = rand(11111,999999);//Call 
                                  $fileName=$source."_".$alphanumeric_val."_".$day.".".$extension; // renameing image
                                  $path = public_path($destinationPath."/" . $fileName);
                                   Image::make($file->getRealPath())->save($path);
                                  // $img->move($destinationPath, $fileName);
      
                                  $thumbpath = "uploads/".$source."/".$year."/".$month."/thumb";
                                 
                                  // make thumbnail image  
                                  crud_master::resize($destinationPath,$thumbpath,$fileName,110,85);
                                  
                                  
                                  $avatar = new Avatar();
                                  $avatar->path = $destinationPath;
                                  $avatar->image = $fileName;
                                  $avatar->source = $source;
                                  $avatar->unique_id = $unique_id; 
                                  $avatar->status = 1;
                                  $avatar->save();
                                  
                         }else{
                                 
                         }
                             
                         
                       $i++;
                      }
      
                return $unique_id;
               
          } 
              
      
              public static function resize($mainpath,$thumbpath,$fileName,$wid,$hei)
              {
                  if (!file_exists($thumbpath)) {
                      File::makeDirectory($thumbpath, 0777, true);
                  }
                  $write = $thumbpath."/".$fileName;
                  //width and height logic
                  $width = ($wid/100)*100;
                  $height = ($hei/100)*100;
                  $image = Image::make(sprintf($mainpath."/%s", $fileName))->resize($width, $height)->save($write);
      
              }
        }


        ';
        echo fwrite($file_model,$model);
        fclose($file_model);
    }

    public function createAvatar($avatar ='avatar'){
        if (!Schema::hasTable($avatar)) {
            // Code to create table
            Schema::create($avatar, function (Blueprint $table) {
                
                $table->increments('id');
                $table->string('path');
                $table->string('image');
                $table->string('source');
                $table->string('unique_id');
                $table->integer('status',false,true);
                $table->timestamps();

            });

          
            
        

        $model_folder = base_path()."/"."app/Models";
        if(!is_dir($model_folder)){
          mkdir($model_folder,0777);
        }
        $file_model = fopen($model_folder."/".ucfirst($avatar).".php","w");

        $model = 
        '<?php

        namespace App\Models;

        use Illuminate\Database\Eloquent\Model;
        use Schema;
        

        class '.ucfirst($avatar).' extends Model
        {
            //
            protected $table = "'.$avatar.'";

            public static function get_img_images($unique_id) {
                $data = (Object) array();
                 if(!empty($unique_id)){
                         $avatar = Avatar::where("unique_id","=",$unique_id)->first();
                      $avatars = Avatar::where("unique_id","=",$unique_id)->get();
                      $avatars_img = array();
                     if(isset($avatars) && count($avatars) > 0 ){
                      foreach ($avatars as $key => $value) {
                        $img = url("/")."/".$value->path."/".$value->image;   
                        $avatars_img[] = (Object) array("image"=>$img);
                      }
                     }
                     $image = url("/")."/".$avatar->path."/".$avatar->image;
                     $data->single_image = $image;
                     $data->images = $avatars_img;
                 }
  
                 return $data;
            }
            public static function get_single_image($unique_id){
                $avatar  = Avatar::where("unique_id","=",$unique_id)->orderBy("id","ASC")->first(); 
                if(!empty($avatar)){
                    $image = url("/")."/".$avatar->path."/".$avatar->image;	
                }else{
                    $image = null;
                }
                
                return $image;
            }
            public static function get_multi_image($unique_id){
                $avatars = Avatar::where("unique_id","=",$unique_id)->get();
                $avatars_img = array();
                            if(isset($avatars) && count($avatars) > 0 ){
                            foreach ($avatars as $key => $value) {
                                $img = url("/")."/".$value->path."/".$value->image;   
                                $avatars_img[] = (Object) array("image"=>$img);
                            }
                            }
                return $avatars_img;
            }
          
            
              
      
              
    }


        ';
        echo fwrite($file_model,$model);
        fclose($file_model);

      }  // not exist
    }
}

