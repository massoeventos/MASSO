<?php

Route::group([
    'domain' => env('ROUTE_SYSTEM', ''),
], function()
{
	Route::group(['namespace' => 'Auth'], function()
	{
		Route::get('/', 				['uses' => 'LoginController@getLogin', 			'as' => 'login.index']);
		Route::get('/recovery', 		['uses' => 'LoginController@forgot', 			'as' => 'login.forgot']);
		Route::post('/recovery', 		['uses' => 'LoginController@postForgot', 		'as' => 'login.forgotPost']);
		Route::get('/recovery/{t}', 	['uses' => 'LoginController@recovery', 			'as' => 'login.recovery']);
		Route::post('/recovery/{t}', 	['uses' => 'LoginController@postRecovery', 		'as' => 'login.recoveryPost']);
		Route::post('/login', 			['uses' => 'LoginController@postLogin', 		'as' => 'login.post']);
		Route::get('/logout', 			['uses' => 'LogoutController@index', 			'as' => 'logout.index']);
	});

	Route::group(['middleware'=>['auth'], 'namespace' => 'Admin'], function(){

		Route::group(['middleware'=>['rbac']], function () {

			Route::get('/panel',			['uses' => 'DashboardController@index', 'as' => 'dashboard.index']);

			// Pagos
			Route::get('/pagos/',    			['uses' => 'PaymentController@index', 'as' => 'payments.index']);
			Route::get('/pagos/{id}/create',   	['uses' => 'PaymentController@create', 'as' => 'payments.create']);
			Route::post('/pagos/{id}/create',   	['uses' => 'PaymentController@store', 'as' => 'payments.store']);
			Route::get('/pagos/{id}', 			['uses' => 'PaymentController@show', 'as' => 'payments.show']);
			Route::post('/pagos/{id}/ticket',		['uses' => 'PaymentController@ticket', 'as' => 'payments.ticket']);
			Route::get('/pagos/{id}/dte',		['uses' => 'PaymentController@dte', 'as' => 'payments.dte']);
			Route::delete('/pagos/{id}',		['uses' => 'PaymentController@destroy', 'as' => 'payments.destroy']);
			Route::post('/pagos/{id}/value',	['uses' => 'PaymentController@updateValue', 'as' => 'payments.updateValue']);

			// Clientes
			Route::get('/clientes',    			['uses' => 'ClientController@index', 'as' => 'clients.index']);
			Route::get('/clientes/create',   	['uses' => 'ClientController@create', 'as' => 'clients.create']);
			Route::post('/clientes/create',   	['uses' => 'ClientController@store', 'as' => 'clients.store']);
			Route::get('/clientes/{id}/edit', 	['uses' => 'ClientController@edit', 'as' => 'clients.edit']);
			Route::post('/clientes/{id}/edit',	['uses' => 'ClientController@update', 'as' => 'clients.update']);
			Route::delete('/clientes/{id}',		['uses' => 'ClientController@destroy', 'as' => 'clients.destroy']);

			Route::get('/eventos',    			['uses' => 'EventController@index', 'as' => 'events.index']);
			Route::get('/eventos/create',   	['uses' => 'EventController@create', 'as' => 'events.create']);
			Route::post('/eventos/create',   	['uses' => 'EventController@store', 'as' => 'events.store']);
			Route::get('/eventos/{id}/edit', 	['uses' => 'EventController@edit', 'as' => 'events.edit']);
			Route::post('/eventos/{id}/edit',	['uses' => 'EventController@update', 'as' => 'events.update']);
			Route::delete('/eventos/{id}',		['uses' => 'EventController@destroy', 'as' => 'events.destroy']);

			Route::get('/inscritos/{id}', 		['uses' => 'EnrollController@index', 'as' => 'enrolls.index']);
			Route::get('/inscritos/{id}/create',['uses' => 'EnrollController@create', 'as' => 'enrolls.create']);
			Route::post('/inscritos/{id}/create',['uses' => 'EnrollController@store', 'as' => 'enrolls.store']);
			Route::get('/inscritos/{id}/{e}', 	['uses' => 'EnrollController@show', 'as' => 'enrolls.show']);

			Route::get('/doc/{e}',    			['uses' => 'FileController@index', 'as' => 'files.index']);
			Route::get('/doc/{e}/create',   	['uses' => 'FileController@create', 'as' => 'files.create']);
			Route::post('/doc/{e}/create',   	['uses' => 'FileController@store', 'as' => 'files.store']);
			Route::get('/doc/{e}/edit/{i}', 	['uses' => 'FileController@edit', 'as' => 'files.edit']);
			Route::post('/doc/{e}/edit/{i}',	['uses' => 'FileController@update', 'as' => 'files.update']);
			Route::delete('/doc/{e}/{i}',		['uses' => 'FileController@destroy', 'as' => 'files.destroy']);


			Route::get('/team/',    			['uses' => 'TeamController@index', 'as' => 'team.index']);
			Route::get('/team/create',   		['uses' => 'TeamController@create', 'as' => 'team.create']);
			Route::post('/team/create',   		['uses' => 'TeamController@store', 'as' => 'team.store']);
			Route::get('/team/edit/{i}', 		['uses' => 'TeamController@edit', 'as' => 'team.edit']);
			Route::post('/team/edit/{i}',		['uses' => 'TeamController@update', 'as' => 'team.update']);
			Route::delete('/team/{i}',			['uses' => 'TeamController@destroy', 'as' => 'team.destroy']);

			Route::get('/expirados',    		['uses' => 'ExpiredController@index', 'as' => 'expired.index']);
			Route::get('/expirados/create',   	['uses' => 'ExpiredController@create', 'as' => 'expired.create']);
			Route::post('/expirados/create',   	['uses' => 'ExpiredController@store', 'as' => 'expired.store']);
			Route::get('/expirados/{id}/edit', 	['uses' => 'ExpiredController@edit', 'as' => 'expired.edit']);
			Route::post('/expirados/{id}/edit',	['uses' => 'ExpiredController@update', 'as' => 'expired.update']);
			Route::delete('/expirados/{id}',	['uses' => 'ExpiredController@destroy', 'as' => 'expired.destroy']);

			Route::get('/encuestas',    		['uses' => 'SurveyController@index', 'as' => 'surveys.index']);
			Route::get('/encuestas/create',   	['uses' => 'SurveyController@create', 'as' => 'surveys.create']);
			Route::post('/encuestas/create',   	['uses' => 'SurveyController@store', 'as' => 'surveys.store']);
			Route::get('/encuestas/{id}/edit', 	['uses' => 'SurveyController@edit', 'as' => 'surveys.edit']);
			Route::post('/encuestas/{id}/edit',	['uses' => 'SurveyController@update', 'as' => 'surveys.update']);
			Route::delete('/encuestas/{id}',	['uses' => 'SurveyController@destroy', 'as' => 'surveys.destroy']);

			// ADMIN
			Route::get('/admin',    			['uses' => 'UserAdminController@index', 'as' => 'g.admin.index']);
			Route::get('/admin/create',   		['uses' => 'UserAdminController@create', 'as' => 'g.admin.create']);
			Route::post('/admin/create',   		['uses' => 'UserAdminController@store', 'as' => 'g.admin.store']);
			Route::get('/admin/{id}/edit', 		['uses' => 'UserAdminController@edit', 'as' => 'g.admin.edit']);
			Route::post('/admin/{id}/edit',		['uses' => 'UserAdminController@update', 'as' => 'g.admin.update']);
			Route::delete('/admin/{id}',		['uses' => 'UserAdminController@destroy', 'as' => 'g.admin.destroy']);


			// LOG
			Route::get('/logs',    						['uses' => 'LogController@index', 'as' => 'g.log.index']);

		});

	});

});

Route::group([
	'domain' => env('ROUTE_WEB', ''),
], function()
{
	Route::group(['namespace' => 'Guest'], function()
	{
		Route::get('/', 				 ['uses' => 'PublicController@index', 		'as' => 'public.index']);
		Route::get('/somos', 			 ['uses' => 'PublicController@about', 		'as' => 'public.about']);
		Route::get('/eventos-anteriores',['uses' => 'PublicController@previously', 	'as' => 'public.previously']);
		Route::get('/contacto',			 ['uses' => 'PublicController@contact', 		'as' => 'public.contact']);
		Route::get('/certificados',		 ['uses' => 'PublicController@certificates', 'as' => 'public.certificates']);
		Route::post('/certificados',	 ['uses' => 'PublicController@certificates', 'as' => 'public.search']);

		Route::get('/descarga/{id}',	 ['uses' => 'PublicController@download', 	'as' => 'public.download']);

		Route::get('/pagos', 			 ['uses' => 'PublicController@payment', 		'as' => 'public.payment']);
		Route::post('/pagos', 			 ['uses' => 'PublicController@processPay', 	'as' => 'public.payment2']);

		Route::any('/webpay/check',		 ['uses' => 'CartController@check', 			'as' => 'cart.validate']);
		Route::any('/webpay/verify',	 ['uses' => 'CartController@verify', 		'as' => 'cart.verify']);
		Route::get('/webpay/error',		 ['uses' => 'CartController@webpayError', 	'as' => 'cart.webpayerror']);
		Route::get('/webpay/exito',		 ['uses' => 'CartController@webpayExito', 	'as' => 'cart.webpayexito']);

		Route::get('/{id}', 			 ['uses' => 'PublicController@event', 		'as' => 'public.event']);
		Route::get('/{id}/register', 	 ['uses' => 'PublicController@register', 	'as' => 'public.register']);
		Route::post('/{id}/register', 	 ['uses' => 'PublicController@process', 	'as' => 'public.process']);
	});
});







