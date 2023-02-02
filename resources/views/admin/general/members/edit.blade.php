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
                    <li class="breadcrumb-item"><a href="{{ route('team.index') }}">Eventos</a></li>
                    <li class="breadcrumb-item active">{{ $title }}</li>
                </ol>
                
                <a href="{{ route('team.index') }}" class="btn btn-danger m-l-15">
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
                            <h4 class="card-title">Editar Miembro</h4>
                        </div>
                        <div class="col-12">
                            <h6 class="card-subtitle">Favor llene todos los campos que aparecen a continuación.</h6>
                        </div>
                    </div>
                    
                    
                    
                    {!! Form::model($member, ['url'=>route('team.update', $member->id), 'method'=>'POST', 'files'=>true, 'class'=>'row']) !!}
                        <div class="col-md-12 loading-wrapper">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="m-t-20">Nombre Miembro</label>
                                    {!! Form::text('name', null, ['class'=>'form-control', 'required'=>'required', 'placeholder'=>'Ej:  Paola Masso' ]) !!}
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="m-t-20">Imágen General</label><br>
                                    <img src="{{ $member->image }}" height="100px;">
                                    <hr>
                                    {!! Form::file('image', null, ['class'=>'form-control' ]) !!}<br>
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Descripción General</label><br>
                                    {!! Form::textarea('description', null, ['required'=>'required', 'class'=>'textarea_editor form-control' ]) !!}<br>
                                </div>

                                <div class="col-md-12 mt-4 text-center">
                                    <button class="btn btn-primary">Editar Miembro</button>
                                </div>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>


@endsection
