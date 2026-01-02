@extends('layouts.auth')

@section('content')

    <section id="wrapper">
        <div class="login-register" style="background-image:url(/images/background/login-register.jpg);">
            <div class="login-box card">
                <div class="card-body">
                    <form method="POST" action="{{ route('login.post') }}" class="form-horizontal form-material text-center" id="loginform">
                        @csrf

                        <div class="logo-wrapper">
                            <img src="/images/logo.jpg">
                        </div>


                        <center>
                            <p class="lead">En Mantención</p>
                        </center>
                        
                    </form>
                    
                </div>
            </div>
        </div>
    </section>

    
        
@endsection