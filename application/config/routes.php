<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

$route['default_controller'] = 'home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// api version 1
# Authenticate
$route['api/v(:num)/auth'] = "api_ver$1/auth/index";
$route['api/v(:num)/auth/(:any)'] = "api_ver$1/auth/$1";

// Controllers
$route['api/v(:num)/(:any)/(:any)'] = "api_ver$1/$2/$3";

# GET --> list records
# POST --> create record
# example: /api/v1/users --> api_ver1/Users/records
$route['api/v(:num)/(:any)'] = "api_ver$1/$2/records";

# GET --> detail record
# PUT --> edit record
# DELETE --> delete record
# example: /api/v1/users/1 --> api_ver1/Users/record
$route['api/v(:num)/(:any)/(:num)'] = "api_ver$1/$2/record";

$route['admin'] = 'admin/dashboard';