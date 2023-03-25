# laravel-dynamic-crud-operation

[![Author](https://img.shields.io/badge/Author-Thilagaraja-blue.svg?style=flat-square)](https://github.com/thilakace)

## Description about package
* This package is useful for Developers to easily create a CRUD (Create, Read, Update, Delete) for API Integration.
* Below are the command to use this feature.
```
composer require thilagaraja/laravelcurdmvc 
```
## System require
* PHP
* Laravel
* MySQL

## add below in config/app.php

* under the provide 
```
Thilagaraja\Laravelcurdmvc\LaravelCurdMvcProvider::class
```
* under the allases
```
 'Input' => Illuminate\Support\Facades\Request::class,
```
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

## Step 1 : Master API for module creation 
* API Name : http://localhost:8000/master
* Method   : POST
* payload  :  

```
{
  "module" : "module name here",
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
* This package will create one master table in your project for unique module validation. The table name is crud_master.
* And each module has an seperate table. 
## Step 2 : Run migration for table creation 
* After run the master module api, below file will be created automatically in your project folder
  - Migration file, Controller file, Module File and routes on web.php file.
  - Need to run following command 
  ```
  php artisan migrate
  ```
* As of now we have only create table migration. If you want to alter table You can manually update the migration file as usual.  
## Step 3 : Use the API End points  
* Now you can download the postman  collection for CRUD Operation   [Download Collection](https://github.com/thilakace/laravelcrudmvc/blob/master/Laravel-crud-mvc-collection.json)
* API End Points
  - List Api      :  GET   : http://localhost:8000/webhook/module_name
  - Store Api     :  POST  : http://localhost:8000/webhook/module_name
  - Edit Api      :  GET   : http://localhost:8000/webhook/module_name/4/edit   // => 4 is primary ID of the table
  - Update Api    :  PUT   : http://localhost:8000/webhook/module_name/4
  - Delete Api    : DELETE : http://localhost:8000/webhook/module_name/4
  - Status Change :   GET  : http://localhost:8000/webhook/module_name/4/{param}/status  // {param} 1 is Active, 0 is InActive, 2 is Delete 

  

