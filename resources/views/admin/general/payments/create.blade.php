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
                    <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Listado de Pagos</a></li>
                    <li class="breadcrumb-item active">{{ $title }}</li>
                </ol>
                
                <a href="{{ route('payments.index') }}" class="btn btn-danger m-l-15">
                    <i class="fa fa-arrow-left"></i>  Volver a Pagos
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
                            <h4 class="card-title">Nuevo Pago para cliente <b>{{ $client->name }}</b></h4>
                        </div>
                        <div class="col-12">
                            <h6 class="card-subtitle">Ingrese los detalles del pago, una vez generado se enviará al cliente un correo a la casilla <b>{{ $client->email }}</b> con el detalle y las instrucciones para ejecutar la transacción.</h6>
                        </div>
                    </div>
                    
                    
                    
                    {!! Form::open(['url'=>route('payments.store', $client->id), 'method'=>'POST', 'class'=>'row']) !!}
                        <div class="col-md-12 loading-wrapper">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="m-t-20">Descripción del Pago</label>
                                    {!! Form::text('description', null, ['class'=>'form-control', 'required'=>'required', 'placeholder'=>'Ej: Inscripción a Tour' ]) !!}
                                </div>
                                <div class="col-md-6">
                                    <label class="m-t-20">Monto</label>
                                    {!! Form::text('amount', null, ['class'=>'form-control number-format', 'placeholder'=>'Ej: $100.000' ]) !!}
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">Descripción del Pago</label>
                                    {!! Form::textarea('comments', null, ['class'=>'form-control']) !!}
                                </div>
                                <div class="col-md-12 mt-3">
                                    <button class="btn btn-primary">Crear Pago y Enviar a Cliente</button>
                                </div>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>


@endsection