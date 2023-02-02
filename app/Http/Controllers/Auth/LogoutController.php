<?php

namespace Masso\Http\Controllers\Auth;

use Auth;
use Masso\User;
use Masso\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Masso\Http\Requests\ClientStoreRequest;
use Masso\Mail\RegisterValidMail;

class LogoutController extends Controller
{
    
    protected $loginPath = '/';

    public function index(Request $request)
    {
        Auth::logout();
        return redirect('/');
    }

}
