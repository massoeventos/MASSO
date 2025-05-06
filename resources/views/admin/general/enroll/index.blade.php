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
                    <a class="btn btn-info  m-l-15" href="{{ route('enrolls.create', $event->id) }}">
                        <i class="fa fa-plus-circle"></i> Inscribir Asistente
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

                    <h4 class="card-title" style="z-index: 1;">
                        <b>Inscritos para: </b>
                        {{ $event->name }} 
                        <small>({{ sizeof($assistants) }} Inscritos)</small>
                        <small>(<b>{{ $total_format }}</b> Total recaudación)</small>
                        <div class="float-right pull-right" style="z-index: 999">
                            <a href="?download" class="btn btn-dark m-l-15">Descargar Inscritos</a>
                        </div>
                    </h4>
                    <h6 class="card-subtitle mb-3" style="z-index: 1;">A continuación se muestran los inscritos al evento.</h6>



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
                                    <th>Fecha</th>
                                    <th>Nombre</th>
                                    <th>RUT</th>
                                    <th>DNI</th>
                                    <th>Email</th>
                                    <th>Ticket</th>
                                    <th>¿Asistió?</th>
                                    <th width='20%'>Acción</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if( sizeof($assistants) > 0 && count($assistants) > 0)

                                    @foreach( $assistants as $assistant )
                                    <tr>
                                        <td>{{ date('d-m-Y H:I', strtotime($assistant->created_at)) }}</td>
                                        <td>{{ $assistant->getName() }}</td>
                                        <td>{{ $assistant->rut_print }}</td>
                                        <td>{{ $assistant->passport }}</td>
                                        <td>{{ $assistant->email }}</td>
                                        <td>{{ $assistant->ticket->name }}</td>
                                        <td><label class="badge badge-dark">No</label></td>
                                        <td>
                                            <a title="Ver Inscripción" class="btn btn-primary btn-sm" href="{{ route('enrolls.show', [$event->id, $assistant->id]) }}"><i class="fa fa-search"></i></a>
                                            {!! Form::open(['class'=>'form-inline', 'url'=>route('events.destroy', $assistant->id), 'method'=>'DELETE', 'style'=>'display:inline']) !!}
                                                <button title="Eliminar Evento" class="btn btn-danger btn-sm" onclick="javascript:return confirm('¿Esta seguro de eliminar este inscrito al evento?')"><i class="fa fa-trash"></i></button>
                                             {!! Form::close() !!}
                                         </td>


                                    </tr>
                                    @endforeach

                                @else
                                    <tr>
                                        <td colspan=4>
                                            No se encontraron asistentes inscritos.
                                        </td>
                                    </tr>
                                @endif

                            </tbody>
                        </table>
                    </div>

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
