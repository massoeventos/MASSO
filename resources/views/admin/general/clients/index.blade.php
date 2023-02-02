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
                    
                    <h4 class="card-title">Listado de Inscritos
                        <div class="float-right pull-right" style="z-index: 999">
                            <a href="?download" class="btn btn-dark m-l-15">Descargar Histórico</a>
                        </div>
                    </h4>
                    <h6 class="card-subtitle mb-3">A continuación se muestran los inscritos registrados en la plataforma.</h6>

                    {!! Form::open(['method'=>'GET', 'class'=>'row']) !!}

                        <div class="col-12">
                            <div class="input-group mb-3">
                                {!! Form::text('search', null, ['class'=>'form-control', 'placeholder'=>'Buscar por nombre o correo.']) !!}
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
                                    <th>RUT</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Teléfono</th>
                                    <th>Ciudad</th>
                                    <th>País</th>
                                    <th>Último Evento</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if( sizeof($clients) > 0 && count($clients) > 0)

                                    @foreach( $clients as $client )

                                    <tr>
                                        <td>{{ $client->passport }}</td>
                                        <td>{{ $client->getName() }}</td>
                                        <td>{{ $client->email }}</td>
                                        <td>{{ $client->phone }}</td>
                                        <td>{{ $client->city }}</td>
                                        <td>{{ $client->country }}</td>
                                        <td><a href="{{ route('enrolls.index', $client->event->id) }}">{{ $client->event->name }}</a></td>
                                    </tr>
                                    @endforeach

                                @else
                                    <tr>
                                        <td colspan=4>
                                            No se encontraron inscritos.
                                        </td>
                                    </tr>
                                @endif
                                
                            </tbody>
                        </table>
                    </div>
                    @if( sizeof($clients) > 0 && sizeof($clients) > 0 )
                    <nav aria-label="navigation" class="pagination-general">
                        {{ $clients->appends(request()->input())->links() }}
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