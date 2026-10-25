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
|	https://codeigniter.com/userguide3/general/routing.html
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
$route['default_controller'] = 'dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Auth
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';
$route['change-password'] = 'auth/changePassword';

// Customers
$route['customers'] = 'customers/index';
$route['customers/create'] = 'customers/create';
$route['customers/suggest'] = 'customers/suggest';
$route['customers/export'] = 'customers/export';
$route['customers/import'] = 'customers/import';
$route['customers/import/process'] = 'customers/importProcess';
$route['customers/(:num)/maps'] = 'customers/saveMapsUrl/$1';
$route['customers/(:num)'] = 'customers/show/$1';
$route['customers/(:num)/edit'] = 'customers/edit/$1';

// Deliveries (admin)
$route['deliveries'] = 'deliveries/index';
$route['deliveries/create'] = 'deliveries/create';
$route['deliveries/store'] = 'deliveries/store';
$route['deliveries/(:num)'] = 'deliveries/show/$1';
$route['deliveries/(:num)/edit'] = 'deliveries/edit/$1';
$route['deliveries/(:num)/update'] = 'deliveries/update/$1';
$route['deliveries/(:num)/items'] = 'deliveries/addItem/$1';
$route['deliveries/(:num)/items/(:num)/remove'] = 'deliveries/removeItem/$1/$2';
$route['deliveries/(:num)/items/(:num)/note'] = 'deliveries/updateItemNote/$1/$2';
$route['deliveries/(:num)/items/(:num)/order'] = 'deliveries/updateItemOrder/$1/$2';
$route['deliveries/(:num)/items/(:num)/receipt'] = 'deliveries/itemReceipt/$1/$2';
$route['deliveries/(:num)/items/(:num)/move/(up|down)'] = 'deliveries/moveItem/$1/$2/$3';
$route['deliveries/(:num)/status'] = 'deliveries/setStatus/$1';
$route['deliveries/(:num)/regenerate'] = 'deliveries/regenerate/$1';
$route['deliveries/(:num)/revoke'] = 'deliveries/revoke/$1';

// Driver (publik, berbasis token)
$route['d/(:any)/items/(:num)/status'] = 'driver/updateItemStatus/$1/$2';
$route['d/(:any)/items/(:num)/receipt'] = 'driver/receipt/$1/$2';
$route['d/(:any)'] = 'driver/view/$1';
