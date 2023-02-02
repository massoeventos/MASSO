@extends('layouts.auth')

@section('content')

    <section id="wrapper">
        <div class="login-register" style="background-image:url(/images/background/login-register.jpg);">
            <div class="login-box card">
                <div class="card-body">
                    {!! Form::open(['url'=>route('login.post'),'method'=>'POST', 'class'=>'form-horizontal form-material text-center', 'id'=>'loginform']) !!}

                        <div class="logo-wrapper">
                            <img src="/images/logo.jpg">
                        </div>


                        <center>
                            <p class="lead">En Mantención</p>
                        </center>
                        
                    {!! Form::close() !!}
                    
                </div>
            </div>
        </div>
    </section>

    
        
@endsection