<?php namespace Thilagaraja\Laravelcurdmvc\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Thilagaraja\Laravelcurdmvc\app\models\User;

class MasterController extends Controller
{
    public function index($module)
    {
        //return User::all();
        if($module){
            return $this->create($module);
        }else{
            return "Module not found";
        }
       
        
    }

    public function create($name){
        $name_class = str_replace(" ", "_", $name);
        $cntrl_name = ucfirst($name_class);
        $table_name = $name_class;
        return $this->done($name_class,$cntrl_name,$table_name);
        //return view('mvc::welcome');
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
                
                    $table->increments("id");
                    $table->integer("status",false,true);
                    $table->timestamps();

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

        $model_folder = base_path()."/"."app/Model";
        if(!is_dir($model_folder)){
        mkdir($model_folder,0777);
        }
        $file_model = fopen($model_folder."/".$name_class.".php","w");

        $model = 
        '<?php

        namespace App\Model;

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
        use App\Model\\'.$name_class.';
        use App\Model\master;
        use App\Model\Avatar;
        use Auth;



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
                    $data["list"] = '.$name_class.'::where("status","!=",2)->paginate(10);
                    $new_column = array("id"); // add as your need
                    $data["table_column"] = '.$name_class.'::getTableColumns("needed",$new_column);
                    $data["form_column"] = '.$name_class.'::getTableColumns();
                    return view("pages/'.$name_class.'/index",$data);
            }
            
            public function store () {
                
                
                $except = array("status","id","created_at","updated_at","created_by","modified_by");
                $column = '.$name_class.'::getTableColumns("except",$except);
                
                /* For getting input values for validation rules*/
                
                $rules = array();
                
                foreach ($column as $key => $value) {
                    if($value =="status" ){

                    }else if($value == "image"){
                    // echo $value;
                        $rules[$value] = "required";
                    }
                    else{
                        $rules[$value] = "required";
                    }
                    
                }

                $validator = Validator::make(Input::all(), $rules);

                if ( $validator->fails() )
                {
                    return Redirect::back()->withErrors($validator)->withInput(Input::all());
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
                            
                                $save_image = master::multiFileUpload($img,"'.$name_class.'");

                                $data->image  = $save_image;

                            }
                            
                        
                        }
                        else{
                        $data->$value  = Input::get($value); 
                        
                        }
                    
                    }
                    $data->status = 0;
                    $data->created_by = Auth::user()->id;
                    $data->modified_by = 0;
                    $data->save();
                    
                    Session::flash("success", "Item has been Stored.");
                }
        
                    return redirect("'.$name_class.'");
                
            }
            public function edit($id) {
                    /**
                     * this is edit process
                     *
                     * 
                     */

                    $data["list"] = '.$name_class.'::where("status","!=",2)->paginate(10);
                    
                    $new_column = array("id"); // add as your need
                    
                    $data["table_column"] = '.$name_class.'::getTableColumns("needed",$new_column);
                    
                    $data["form_column"] = '.$name_class.'::getTableColumns();
                    
                    $data["item"] = '.$name_class.'::find($id);
                    
                    return view("pages/'.$name_class.'.index",$data);

            }
            
            public function update ($id) {
                    
                    /**
                     *  For getting input values for validation rules
                     *
                     */

                $except = array("status","id","created_at","updated_at","created_by","modified_by");
                $column = '.$name_class.'::getTableColumns("except",$except);
                
                /* For getting input values for validation rules*/
                
                $rules = array();
                
                foreach ($column as $key => $value) {
                        if($value =="status" || $value =="id" || $value=="created_at" || $value=="updated_at" || $value =="remember_token"){

                        }else if($value == "image"){
                        
                            $rules[$value] = "required";
                        }
                        else{
                            $rules[$value] = "required";
                        }
                        
                    }

                    $validator = Validator::make(Input::all(), $rules);

                    if ( $validator->fails() )
                    {
                        return Redirect::back()->withErrors($validator)->withInput(Input::all());
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
                                
                                    $save_image = master::multiFileUpload($img,"'.$name_class.'",$data->image);

                                    $data->image  = $save_image; 
                                }
                                
                                
                            }
                            else{ 

                            $data->$value  = Input::get($value); 
                            
                            }
                        
                        }

                            $data->save();
                            
                            Session::flash("success", "Item has been updated."); 
                    }

                    return redirect("'.$name_class.'");

            }
            public function delete ($id) {
                    /**
                     * this is delete process
                     */

                    $data  = '.$name_class.'::find($id);
                    $data->status = 2;
                    $data->save();
                    Session::flash("success", "Item has been successfully deleted");
                    
                    return redirect("'.$name_class.'");
            }
            public function status ($id,$param) {
                /**
                     * this is status change process
                    */
                $data  = '.$name_class.'::find($id);
                $data->status = $param;
                $data->save();
                
                Session::flash("success", "Status has been successfull changed");
                return redirect("'.$name_class.'");
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
            public function lists () {
                    $list  = '.$name_class.'::where("status","!=",2)->get();
                    $res = array("status"=>"success","message"=>"","list"=>$list);
                    echo json_encode($res);
            }

        }

        ';
        echo fwrite($file,$Controller);
        fclose($file);



        $file2 = base_path(). "/routes/web.php";

        $routes = file_get_contents($file2);

        $routes .= 
        '// below routes for '.$name.'
        Route::get("/'.$name_class.'", "Master\\'.$cntrl_name.'Controller@index");
        Route::get("/'.$name_class.'/create", "Master\\'.$cntrl_name.'Controller@create");
        Route::post("/'.$name_class.'", "Master\\'.$cntrl_name.'Controller@store");
        Route::get("/'.$name_class.'/{id}/edit", "Master\\'.$cntrl_name.'Controller@edit");
        Route::put("/'.$name_class.'/{id}", "Master\\'.$cntrl_name.'Controller@update");
        Route::get("/'.$name_class.'/{id}/delete", "Master\\'.$cntrl_name.'Controller@delete");
        Route::get("/'.$name_class.'/{id}/delete_img", "Master\\'.$cntrl_name.'Controller@delete_img");
        Route::get("/'.$name_class.'/{id}/{param}/status", "Master\\'.$name_class.'Controller@status");
        Route::get("/'.$name_class.'_list", "Master\\'.$cntrl_name.'Controller@lists");
        ';

        file_put_contents($file2, $routes);

        //  $angular_app_file = base_path()."/public/angular/app.js";

        // $angular_app = file_get_contents($angular_app_file);
        // $angular_app .= 
        // '// below for angularjs cntrl 
        // app.controller("'.$cntrl_name.'", function ($scope,GenFactory) {
            
        //            GenFactory.get_List("'.$cntrl_name.'_list").then(function(result){
        //                       $scope.listings = result.list;
                            
        //               });

        // });
        // ';

        //  file_put_contents($angular_app_file, $angular_app);


        $view_folder = base_path()."/"."resources/views/pages";
        if(!is_dir($view_folder)){
        mkdir($view_folder,0777);
        }


        $view_folder  = base_path()."/"."resources/views/pages/".$name_class;
        if (!file_exists($view_folder)) {
            mkdir($view_folder, 0777, true);
        }

        $index = fopen(base_path()."/"."resources/views/pages/".$name_class."/index.blade.php","w");
       

        $views = '
        @extends("pages.crud_app")

        @section("content")

            <div class="row" ng-controller="'.$name_class.'Cntrl">
            <div class="col-lg-6 col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">

                        <div class="pull-left">
                            <span> <?php if (isset($item)) { echo "Update";}else{ echo "Create";}?>  '.$name_class.'</span>
                        </div>
                        <div class="pull-right">
                        <!-- <a class="text-right btn btn-xs btn-primary" href="/'.$name_class.'">List Items</a> -->
                        </div>
                    <div class="clearfix"></div>

                    </div>

                    <div class="panel-body">
                        @if (isset($errors) && count($errors) > 0 )
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                {{ $error }}<br>        
                            @endforeach
                        </div>
                        @endif

                <?php 
                if(isset($item)){
                ?>
                {{ Form::model($item, array("url" => array("/'.$name_class.'", $item->id), "method" => "PUT" , "class" => "form-horizontal","files"=>"true")) }}
                <?php
                }else{
                ?>
                {{ Form::open(array("url" => "/'.$name_class.'","method"=>"POST","class"=>"form-horizontal","files"=>"true")) }}
                <?php
                }
                ?>
                            

                            <?php

                                if(isset($form_column)){
                                    foreach ($form_column as $value) {
                                    if($value =="status" || $value =="id" || $value=="created_at" || $value=="updated_at" || $value=="created_by" || $value=="modified_by" || $value =="remember_token"){


                                        }
                                        else if ($value == "image" && isset($item)){  ?>

                                    <div class="form-group">
                                        <label for="name" class="col-md-4 control-label"><?php echo ucfirst(str_replace("_"," ",$value)); ?></label>

                                        <div class="col-md-6" ng-show="input_file">
                                            <input id="name" type="file" class="form-control" name="<?php echo $value;?>[]"   autofocus multiple>

                                            @if ($errors->has($value))
                                                <span class="help-block">
                                                    <strong class="text-danger">{{ $errors->first($value) }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="col-md-6" ng-hide="input_file">
                                        <ul style="list-style: none">
                                        
                                        <?php 
                                            $images = \App\Model\Avatar::where("unique_id","=",$item->image)->get();
                                            if(isset($images)){
                                            foreach ($images as $key => $value) {
                                                $src = "/".$value->path."/".$value->image;
                                                ?>
                                                <li style="float:left" >
                                                    <img src="<?php echo $src; ?>"  class="img-thumbnail"/>
                                                    <p class="text-center"><a href="/news/<?php echo $value->id;?>/delete_img" onclick="return confirm(\'Are you sure?\')" class="btn btn-xs btn-circle btn-lg d_btn"><i class="ion ion-ios-trash-outline"></i></a> </p> 
                                                </li>
                                                
                                                <?php
                                            }
                                            }
                                        ?>
                                            
                                                
                                            </ul>
                                            <p><button type="button" ng-click="input_file=true" class="btn btn-xs btn-primary">Add More</button></p>
                                        </div>
                                    </div>

                                    <?php
                                        }

                                        else if ($value == "image"){  ?>

                                    <div class="form-group">
                                        <label for="name" class="col-md-4 control-label"><?php echo ucfirst(str_replace("_"," ",$value)); ?></label>

                                        <div class="col-md-6">
                                            <input id="name" type="file" class="form-control" name="<?php echo $value;?>[]"   autofocus multiple>

                                            @if ($errors->has($value))
                                                <span class="help-block">
                                                    <strong class="text-danger">{{ $errors->first($value) }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <?php

                                    }else{

                                    ?>


                                <div class="form-group">
                                        <?php 
                                        $text_field_name =  ucfirst(str_replace("_"," ",$value));
                                        $placeholder = "Enter a ".$text_field_name;
                                        ?>
                                        <label for="name" class="col-md-4 control-label"><?php echo $text_field_name; ?></label>

                                        <div class="col-md-6">
                                        <!--   <input id="name" type="text" class="form-control" name="<?php //echo $value;?>" value="{{ old($value) }}"  autofocus placeholder="Enter a value"> -->
                                            
                                            {{ Form::text($value, null, array("class" => "form-control" , "placeholder" => $placeholder)) }}

                                            @if ($errors->has($value))
                                                <span class="help-block">
                                                    <strong class="text-danger">{{ $errors->first($value) }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                </div>


                            <?php
                                    }
                                    }
                                }
                                ?>
                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button type="submit" class="btn btn-primary">
                                            <?php if (isset($item)) { echo "Update";}else{ echo "Create";}?>
                                        </button>
                                    </div>
                                </div>
                            {!! Form::close() !!}
                    </div>
                </div>
            </div>
                <div class="col-lg-6 col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="pull-left">
                                <span>'.$name_class.'</span>
                            </div>
                            <div class="pull-right">
                            <a class="text-right btn btn-xs btn-primary" href="/'.$name_class.'">Add New</a>
                            </div>
                            <div class="clearfix"></div>
                        </div>

                        <div class="panel-body">
                            @if (Session::has("success"))
                                <div class="alert alert-info">{{ Session::get("success") }}</div>
                            @endif
                        <div style="overflow: auto">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                <th>Action</th>
                                </thead>
                                <tbody>
                                    <tr  dir-paginate="listing in listings | filter:q | itemsPerPage:15 ">
                                        <td><span ng-bind="listing.id"></span></td>
                                        <td><span ng-bind="listing.name"></span></td>
                                        <td><span ng-if="listing.status ==1">Active</span><span ng-if="listing.status ==0">InActive</span></td>
                                        <td>
                                            <a href="/'.$name_class.'/@{{listing.id}}/edit" class="btn btn-xs  btn-circle btn-lg e_btn" data-tooltip="Edit"><i class="ion ion-edit" ></i></a>
                                            <a href="/'.$name_class.'/@{{listing.id}}/delete" onclick="return confirm(\'Are you sure you want to delete ?\')" class="btn btn-xs  btn-circle btn-lg d_btn" data-tooltip="Delete"><i class="ion ion-ios-trash-outline"></i></a>
                                        
                                            <a href="/'.$name_class.'/@{{listing.id}}/1/status" class="btn btn-xs  btn-circle btn-lg a_btn" onclick="return confirm(\'you want active now?\')" ng-if="listing.status==0" data-tooltip="Go to active"><i class="ion ion-android-checkmark-circle" ></i></a>
                                        
                                            <a href="/'.$name_class.'/@{{listing.id}}/0/status" class="btn btn-xs  btn-circle btn-lg ina_btn"  onclick="return confirm(\'you want Inactive now?\')"  ng-if="listing.status==1" data-tooltip="Go to InActive"><i class="ion ion-minus-circled"></i></a>
                                            

                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                        <div class="text-center">
                            <dir-pagination-controls
                                direction-links="true"
                                boundary-links="true" >
                            </dir-pagination-controls>
                        </div>
                        </div>
                    </div>
                </div>
            </div>

        @endsection

        ';
        echo fwrite($index,$views);
        fclose($index);
        
        // layout page
        $crud_app_blade = fopen(base_path()."/"."resources/views/pages/crud_app.blade.php","w");

        $crud_app = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <title>Bootstrap Example</title>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
          <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
          <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
        </head>
        <body>
        
        <div class="jumbotron text-center">
          <h1>'.$name.'</h1>
        </div>
          
        <div class="container">
          <div class="row">
              @yield("content")
          </div>
        </div>
        
        </body>
        </html>
        
        ';

        echo fwrite($crud_app_blade,$crud_app);
        fclose($crud_app_blade);

        return " ".$name." has been create successfully";

    }
}

