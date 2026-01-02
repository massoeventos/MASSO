@extends('layouts.auth')

@section('content')

    <section id="wrapper">
        <div class="login-register">
            <div class="login-box card">
                <div class="card-body">
                    <form method="POST" action="{{ route('login.post') }}" class="form-horizontal form-material text-center" id="loginform">
                        @csrf

                        <div class="logo-wrapper">
                            <img src="/images/logo.jpg">
                        </div>


                        @if(Session::has('success_alert'))
                            <div class="alert alert-success">{!! Session::get('success_alert') !!}</div>
                        @elseif(Session::has('error_alert'))
                            <div class="alert alert-danger">{!! Session::get('error_alert') !!}</div>
                        @elseif( count($errors->all()) > 0)
                            <div class="alert alert-danger @if( count($errors->all()) <= 0) display-hide @endif">
                                <button class="close" data-close="alert"></button>
                                @if( count($errors->all()) > 0)
                                @foreach ($errors->all() as $error)
                                    {{ $error }}</br>
                                @endforeach
                                @else
                                @endif
                            </div>
                        @endif

                        <div class="form-group m-t-40">
                            <div class="col-xs-12">
                                <input type="text" name="rut" value="{{ old('rut') }}" class="form-control rut" placeholder="ID Colaborador" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
                            </div>
                        </div>
                        
                        <div class="m-t-30 text-center">
                            <div class="col-xs-12 p-b-20">
                                <button class="btn btn-primary btn-lg btn-block text-uppercase btn-rounded" type="submit">Iniciar Sesión</button>
                            </div>
                        </div>

                        <div class="form-group m-t-1 text-center">
                            <a href="/recovery">Recuperar Contraseña</a>
                        </div>
                        
                    </form>
                    
                </div>
            </div>
        </div>
    </section>

    
        
@endsection
