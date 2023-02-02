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
                    <li class="breadcrumb-item"><a href="{{ route('events.index') }}">Eventos</a></li>
                    <li class="breadcrumb-item active">Listado Documentos</li>
                </ol>
                @if( $authUser->canDo('files.create') )
                    <a class="btn btn-info  m-l-15" href="{{ route('files.create', $event->id) }}">
                        <i class="fa fa-plus-circle"></i> Añadir Documento
                    </a>
                @endif  
                <a class="btn btn-danger  m-l-15" href="{{ route('events.index') }}">
                    <i class="fa fa-arroy-left"></i> Volver
                </a>
            </div>
        </div>

    </div>
	
    <div class="row">
        <div class="col-12">
            @include('admin.common.flash')
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    
                    <h4 class="card-title">{{ $title }}</h4>
                    <h6 class="card-subtitle mb-3">A continuación se muestran los documentos del evento: {{ $event->name }}.</h6>

                    {!! Form::open(['method'=>'GET', 'class'=>'row']) !!}

                        <div class="col-12">
                            <div class="input-group mb-3">
                                {!! Form::text('search', null, ['class'=>'form-control', 'placeholder'=>'Buscar por nombre.']) !!}
                                <div class="input-group-append">
                                    <button class="btn btn-info" type="button">Filtrar</button>
                                </div>
                            </div>
                        </div>

                    {!! Form::close() !!}

                    <div class="table-responsive mt-1">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Documento</th>
                                    <th>Fecha Creación</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if( sizeof($files) > 0 && count($files) > 0)

                                    @foreach( $files as $file )
                                    <?php $file->checkUUID() ?>
                                    <tr>
                                        <td>{{ $file->id }}</td>
                                        <td>{{ $file->name }}</td>
                                        <td> <a target="_BLANK" href="{{ $file->getPath() }}"><label class="btn btn-xs btn-dark"><i class="fa fa-file"></i></label></a> </td>
                                        <td>{{ $file->getCreatedString() }}</td>
                                        <td>
                                            <a class="btn btn-success btn-sm" href="{{ route('files.edit', [$event->id, $file->id]) }}">Editar</a>
                                            {!! Form::open(['class'=>'form-inline', 'url'=>route('files.destroy', [$event->id, $file->id]), 'method'=>'DELETE', 'style'=>'display:inline']) !!}
                                                <button class="btn btn-danger btn-sm" onclick="javascript:return confirm('¿Esta seguro de eliminar este evento expirado?')">Eliminar</button>
                                             {!! Form::close() !!}
                                         </td>
                                        
                                        
                                    </tr>
                                    @endforeach

                                @else
                                    <tr>
                                        <td colspan=4>
                                            No se encontraron documentos asociados al evento {{ $event->name }}.
                                        </td>
                                    </tr>
                                @endif
                                
                            </tbody>
                        </table>
                    </div>
                    @if( sizeof($files) > 0 && sizeof($files) > 0 )
                    <nav aria-label="navigation" class="pagination-general">
                        {{ $files->appends(request()->input())->links() }}
                    </nav>
                    @endif
                </div>
            </div>
        </div>
    </div>


@endsection

@section('footer')
    <script type="text/javascript">
        $('#mdate').bootstrapMaterialDatePicker({ weekStart: 1, time: false });
    </script>
@endsection