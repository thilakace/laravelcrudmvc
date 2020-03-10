<?php namespace Thilagaraja\Laravelcurdmvc\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Thilagaraja\Laravelcurdmvc\app\models\User;

class MasterController extends Controller
{
    public function index()
    {
        return User::all();
    }
}

