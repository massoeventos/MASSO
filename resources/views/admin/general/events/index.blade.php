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
                    <li class="breadcrumb-item active">{{ $title }}</li>
                </ol>
                @if( $authUser->canDo('events.create') )
                    <a class="btn btn-info  m-l-15" href="{{ route('events.create') }}">
                        <i class="fa fa-plus-circle"></i> Añadir Evento
                    </a>
                @endif  
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
                    <h6 class="card-subtitle mb-3">A continuación se muestran los eventos a ser procesados por el sitio web.</h6>

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
                                    <th>Lugar</th>
                                    <th width='14%'>Inicio</th>
                                    <th width='14%'>Término</th>
                                    <th>Visible</th>
                                    <th width='18%'>Acción</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if( sizeof($events) > 0 && count($events) > 0)

                                    @foreach( $events as $event )
                                    <?php $event->checkSlug() ?>
                                    <tr>
                                        <td>{{ $event->id }}</td>
                                        <td>{{ $event->name }}</td>
                                        <td><small>{{ $event->location }}</small></td>
                                        <td>{{ $event->getInitString('short') }}</td>
                                        <td>{{ $event->getFinishString('short') }}</td>
                                        <td>{!! $event->isVisible() !!}</td>
                                        <td>
                                            @if( $event->status != 2 )
                                            <a title="Editar Evento" class="btn btn-warning btn-sm" href="{{ route('events.edit', [$event->id]) }}"><i class="fa fa-edit"></i></a>
                                            <a title="Documentos de Evento" class="btn btn-info btn-sm" href="{{ route('files.index', [$event->id]) }}"><i class="fa fa-download"></i></a>
                                            @endif
                                            <a title="Ver Usuarios Inscritos" class="btn btn-dark btn-sm" href="{{ route('enrolls.index', [$event->id]) }}"><i class="fa fa-users"></i></a>
                                            <a title="Ver Encuestas" class="btn btn-success btn-sm" href="{{ route('surveys.index', ['search'=>$event->id]) }}"><i class="mdi-poll mdi"></i></a>
                                            @if( $event->status != 2 )
                                            {!! Form::open(['class'=>'form-inline', 'url'=>route('events.destroy', $event->id), 'method'=>'DELETE', 'style'=>'display:inline']) !!}
                                                <button title="Archivar Evento" class="btn btn-danger btn-sm" onclick="javascript:return confirm('¿Esta seguro de archivar este evento? Al realizar esta acción el evento se dará por finalizado y pasará a ser parte del módulo Eventos Expirados. Los datos de inscritos y encuestas seguirán estando activos.')"><i class="fa fa-trash"></i></button>
                                             {!! Form::close() !!}
                                             @else
                                            {!! Form::open(['class'=>'form-inline', 'url'=>route('events.destroy', $event->id), 'method'=>'DELETE', 'style'=>'display:inline']) !!}
                                                <button title="Eliminar Evento" class="btn btn-danger btn-sm" onclick="javascript:return confirm('¿Esta seguro de eliminar este evento? La información asociada dejará de estar disponible.')"><i class="fa fa-trash"></i></button>
                                             {!! Form::close() !!}
                                             @endif
                                         </td>
                                        
                                        
                                    </tr>
                                    @endforeach

                                @else
                                    <tr>
                                        <td colspan=4>
                                            No se encontraron eventos asociados.
                                        </td>
                                    </tr>
                                @endif
                                
                            </tbody>
                        </table>
                    </div>
                    @if( sizeof($events) > 0 && sizeof($events) > 0 )
                    <nav aria-label="navigation" class="pagination-general">
                        {{ $events->appends(request()->input())->links() }}
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