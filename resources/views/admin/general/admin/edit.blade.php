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
                    <li class="breadcrumb-item"><a href="{{ route('g.admin.index') }}">Listado de Administradores</a></li>
                    <li class="breadcrumb-item active">{{ $title }}</li>
                </ol>
                
                <a href="{{ route('g.admin.index') }}" class="btn btn-danger m-l-15">
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
                            <h4 class="card-title">Editar Administrador {{ $user->name }}</h4>
                        </div>
                        <div class="col-12">
                            <h6 class="card-subtitle">Favor llene todos los campos que aparecen a continuación.</h6>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('g.admin.update', $user->id) }}" class="row">
                        @csrf
                        <input type="hidden" name="id" value="{{ $user->id }}">
                        <div class="col-md-12 loading-wrapper">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="m-t-20">ID Inicio Sesión</label>
                                    <input type="text" name="rut" value="{{ old('rut', $user->rut) }}" class="form-control" placeholder="Ej: 19222999-0 o lperez">
                                </div>
                                <div class="col-md-6">
                                    <label class="m-t-20">Nombre</label>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" placeholder="Ej: Luis Perez">
                                </div>
                                <div class="col-md-6">
                                    <label class="m-t-20">Correo Electrónico</label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" placeholder="Ej: prueba@massoeventos.cl">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="m-t-20">Contraseña</label>
                                    <input type="password" name="password" class="form-control">
                                </div>
                                <div class="col-md-12 mt-3">
                                    <button class="btn btn-primary">Editar Administrador</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection