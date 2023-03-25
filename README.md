# laravel-dynamic-crud-operation

[![Author](https://img.shields.io/badge/Author-Thilagaraja-blue.svg?style=flat-square)](https://github.com/thilakace)

## System require
* PHP
* Laravel
* MySQL

## add below in config/app.php

* under the provide 

Thilagaraja\Laravelcurdmvc\LaravelCurdMvcProvider::class

* under the allases

 'Input' => Illuminate\Support\Facades\Request::class,

## Need to disable csrf 
* App\Http\Middleware\VerifyCsrfToken.php

```
    protected $except = [
        'webhook/*', // Disable CSRF protection for routes matching the "webhook/*" pattern
    ];
```
* install below package for the following error => class 'form' not found 
```
composer require laravelcollective/html
```

# CRUD Operation API's

## Master API for module creation 
* API Name : http://localhost:8000/master
* Method   : POST
* payload  :  

```
{
  "module" : "new7",
  "fields" : [
    {
      "column" : "name",  
      "date_type" : "string"
    },
    {
      "column" : "location",
      "date_type" : "string"
    },
    {
      "column" : "No of Parking",
      "date_type" : "integer"
    }
    ]
}


```
* Column name 'name' is mandatory for all modules,
* Data types : string, integer
* After run the master module api, below file will be created automatically in your project folder
  - Migration file, Controller file, Module File and routes on web.php file.
  - Need to run following command 
  ```
  php artisan migrate
  ```
* Now you can download the postman  collection for CRUD Operation   [Download Collection](https://github.com/thilakace/laravelcrudmvc/blob/master/Laravel-crud-mvc-collection.json)
  

