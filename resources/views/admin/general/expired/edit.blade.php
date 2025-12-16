@extends('layouts.panel')

@section('content')
    
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h4 class="text-themecolor">{{ $title }}</h4>
        </div>
        <div class="col-md-7 align-self-center text-right">
            <div class="d-flex justify-content-end align-items-center">
                <ol class="breadcrumb d-none d-lg-flex">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('events.expired') }}">Eventos Expirados</a></li>
                    <li class="breadcrumb-item active">Editar Evento Expirado</li>
                </ol>
                
                <a href="{{ route('events.expired') }}" class="btn btn-danger m-l-15">
                    <i class="fa fa-arrow-left"></i>  Volver
                </a>

            </div>
        </div>
    </div>

    
    <div class="row">
        <div class="col-12 flash-container">
            @include('admin.common.flash')
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <h4 class="card-title">{{ $title }}</h4>
                        </div>
                        <div class="col-12">
                            <h6 class="card-subtitle">Favor llene todos los campos que aparecen a continuación.</h6>
                        </div>
                    </div>
                    
                    
                    
                    {!! Form::model($expired, ['url'=>route('expired.update', $expired->id), 'method'=>'POST', 'files'=>true, 'class'=>'row']) !!}
                        <div class="col-md-12 loading-wrapper">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="m-t-20">Nombre Evento</label>
                                    {!! Form::text('name', null, ['class'=>'form-control', 'required'=>'required', 'placeholder'=>'Ej: Simposio de Salud' ]) !!}
                                </div>
                                <div class="col-md-6">
                                    <label class="m-t-20">Ubicación</label>
                                    {!! Form::text('location', null, ['class'=>'form-control', 'required'=>'required', 'placeholder'=>'Ej:  Clínica Alemana' ]) !!}
                                </div>
                                <div class="col-md-6">
                                    <label class="m-t-20">Fecha de Inicio</label>
                                    {!! Form::date('date_init', date('Y-m-d', strtotime($expired->date_init)), ['class'=>'date form-control', 'required'=>'required', 'placeholder'=>'Ej: 2019-08-01' ]) !!}
                                </div>
                                <div class="col-md-6">
                                    <label class="m-t-20">Fecha de Término</label>
                                    {!! Form::date('date_finish', date('Y-m-d', strtotime($expired->date_finish)), ['class'=>'date form-control', 'required', 'placeholder'=>'Ej: 2019-08-10' ]) !!}
                                </div>
                                <div class="col-md-6">
                                    <label class="m-t-20">Imágen Actual</label><br>
                                    <img src="{{ $expired->photo }}" class="img-responsive img">
                                </div>
                                <div class="col-md-6">
                                    <label class="m-t-20">Imágen General</label><br>
                                    {!! Form::file('photo', null, [ 'class'=>'form-control' ]) !!}<br>
                                </div>
                                <div class="col-md-12 mt-4 text-center">
                                    <button class="btn btn-primary">Editar Evento Expirado</button>
                                </div>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>


@endsection

@section('footer')
    <script type="text/javascript">
        $('.date').bootstrapMaterialDatePicker({ weekStart: 1, time: false });
    </script>
@endsection