@extends('layouts.auth')

@section('content')

    <section id="wrapper">
        <div class="login-register">
            <div class="login-box card">
                <div class="card-body">
                    {!! Form::open(['url'=>route('login.forgotPost'),'method'=>'POST', 'class'=>'form-horizontal form-material text-center', 'id'=>'loginform']) !!}

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

                        <p>Para recuperar su acceso ingrese a continuación el correo electrónico asociado a la cuenta. Recibirá un mensaje con las instrucciones para procesar el cambio de su contraseña.</p>

                        <div class="form-group m-t-30">
                            <div class="col-xs-12">
                                {!! Form::text('rut', null, ['class'=>'form-control rut', 'placeholder'=>'ID Colaborador', 'required'=>'required']) !!} 
                            </div>
                        </div>
                        
                        <div class="m-t-30 text-center">
                            <div class="col-xs-12 p-b-20">
                                <button class="btn btn-primary btn-lg btn-block text-uppercase btn-rounded" type="submit">Recuperar Contraseña</button>
                            </div>
                        </div>
                        
                    {!! Form::close() !!}
                    
                </div>
            </div>
        </div>
    </section>

    
        
@endsection