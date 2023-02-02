<?php

namespace Masso\Http\Controllers\Auth;

use Masso\User;
use Masso\Region;
use Validator;
use Masso\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Masso\Log;
use Masso\Http\Requests\ClientStoreRequest;
use Masso\Mail\RegisterValidMail;

class RegisterController extends Controller
{
    
    protected $auth;
    

    public function __construct( Request $request )
    {
        parent::__construct( $request );
        $this->middleware('guest', ['except' => 'getLogout']);
    }
    
    public function index(){
        $regions = Region::pluck('name', 'id');
        $comunas = Region::getCities( old('regione_id') );
        return view('auth.register', compact('regions','comunas'));
    }


    public function post(ClientStoreRequest $request){

        $data = $request->all();
        $data['firstname'] = $data['name'];
        $data['username'] = $data['email'];
        $data['password'] = bcrypt($request->password);
        $data['is_admin'] = 0;
        $data['is_active'] = 1;
        $data['enabled']   = 1;


        if( User::create($data) ):
            \Session::flash('success_alert', 'Su cuenta ha sido creada exitosamente. Ahora puede iniciar sesión');
            return \Redirect::route('login.index');
        endif;

        \Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();
    }

}
