<?php
namespace Masso\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Masso\Http\Requests\ClientStoreRequest;
use Masso\Mail\RecoveryMailPassword;
use Masso\Http\Controllers\Controller;
use Masso\Log;
use Masso\User;
use Masso\UserRecovery;
use Validator;
use Auth;


class LoginController extends Controller
{
    
    use ThrottlesLogins;
    protected $loginPath = '/';
    protected $auth;
    protected $registrar;
    

    public function __construct( Request $request )
    {
        parent::__construct( $request );
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'rut' => 'required|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }


    public function init()
    {   
        return view('auth.init');
    }


    public function getLogin()
    {   
        return view('auth.login');
    }

    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'rut' => 'required', 'password' => 'required',
        ]);

        $user = User::where('rut', $request->rut)->first();

        if ($this->hasTooManyLoginAttempts($request)){
            Log::create(['area'=>'Sistema', 'module'=>'Sesión', 'action'=>'Inicio de sesión fallido '.$request->rut, 'user_id'=>0]);
            \Session::flash('error_alert', 'Ha tenido muchos intentos fallidos. Por favor espere un minuto para seguir intentando.');
            return \Redirect::back()->withInput(); 
        }

        $this->incrementLoginAttempts($request);

        if( is_null($user) ):
            Log::create(['area'=>'Sistema', 'module'=>'Sesión', 'action'=>'Inicio de sesión fallido '.$request->rut, 'user_id'=>0]);
           \Session::flash('error_alert', 'La cuenta ingresada no existe.');
            return \Redirect::back()->withInput();
        endif;

        $credentials = $request->only('rut', 'password');

        if ( Auth::attempt(['rut'=>$credentials['rut'], 'password'=>$credentials['password']], $request->has('remember'))):
            $this->clearLoginAttempts($request);
            \Session::flash('success_alert', 'Bienvenido! Su sesión ha sido iniciada exitosamente.');
            Log::create(['area'=>'Sistema', 'module'=>'Sesión', 'action'=>'Inicio de sesión exitoso', 'user_id'=>\Auth::user()->id]);
            return redirect()->intended(route('dashboard.index'));
        endif;

        Log::create(['area'=>'Sistema', 'module'=>'Sesión', 'action'=>'Inicio de sesión fallido '.$credentials['rut'], 'user_id'=>0]);
        return redirect()->route('login.index')
                    ->withInput($request->only('rut', 'remember'))
                    ->withErrors([
                        'rut' => $this->getFailedLoginMessage(),
                    ]);
    }


    public function forgot()
    {   
        return view('auth.forgot');
    }

    public function recovery( $token )
    {   
        $recovery = UserRecovery::where('token', $token)->where('used', 0)->first();

        if( empty($recovery) ):
            \Session::flash('error_alert', 'Token de recuperación expirado');
            return \Redirect::route('login.forgot')->withInput();
        endif;

        $recovery = UserRecovery::where('token', $token)->first();

        if( empty($recovery) ):
            \Session::flash('error_alert', 'Token de recuperación expirado');
            return \Redirect::route('login.forgot')->withInput();
        endif;

        return view('auth.recovery', compact('recovery'));
    }


    public function postForgot( Request $request )
    {   

        $data = $request->get('rut', false);

        if( empty($data) ):
            \Session::flash('error_alert', 'La cuenta ingresada no existe.');
            return \Redirect::route('login.forgot')->withInput();
        endif;

        $user = User::where('rut', $request->rut)->orWhere('email', $request->rut)->first();

        if( empty($user) ):
            \Session::flash('error_alert', 'La cuenta ingresada no existe.');
            return \Redirect::route('login.forgot')->withInput();
        endif;

        $recovery = UserRecovery::create(['used'=>0, 'user_id'=>$user->id, 'token'=>UserRecovery::genToken()]);
        \Mail::to($user->email)->bcc('oscaremilio.bravo@gmail.com')->send(new RecoveryMailPassword($recovery));
        
        \Session::flash('success_alert', 'Se han enviado las instrucciones vía correo para recuperar su contraseña.');
        return \Redirect::route('login.forgot');
    }


    public function postRecovery( Request $request, $token )
    {   
        $data = $request->all();
        $recovery = UserRecovery::where('token', $token)->where('used', 0)->first();

        if( empty($recovery) ):
            \Session::flash('error_alert', 'Token de recuperación expirado');
            return \Redirect::route('login.recovery', $token)->withInput();
        endif;

        $recovery = UserRecovery::where('token', $token)->first();

        if( empty($recovery) ):
            \Session::flash('error_alert', 'Token de recuperación expirado');
            return \Redirect::route('login.recovery', $token)->withInput();
        endif;


        $recovery->user->password = bcrypt($data['password']);
        $recovery->user->save();

        $recovery->used = 1; 
        $recovery->save();

        \Session::flash('success_alert', 'Se contraseña ha sido modificada exitosamente');
        return \Redirect::route('login.index');

    }


    private function username(){
        return 'username';
    }


    protected function getFailedLoginMessage()
    {
        return trans('auth.failed');
    }


    public function getLogout(Request $request)
    {
        Auth::logout();
        return redirect('/');
    }


    public function loginPath()
    {
        return property_exists($this, 'loginPath') ? $this->loginPath : '/auth/login';
    }



}
