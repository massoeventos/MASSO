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
                @if( $authUser->canDo('expired.create') )
                    <a class="btn btn-info  m-l-15" href="{{ route('expired.create') }}">
                        <i class="fa fa-plus-circle"></i> Añadir Evento Expirado
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
                    <h6 class="card-subtitle mb-3">A continuación se muestran los eventos expirados que se muestran en la sección de eventos anteriores del sitio web.</h6>

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
                                    <th>Fecha Expiración</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if( sizeof($expired) > 0 && count($expired) > 0)

                                    @foreach( $expired as $expire )

                                    <tr>
                                        <td>{{ $expire->id }}</td>
                                        <td>{{ $expire->name }}</td>
                                        <td>{{ $expire->location }}</td>
                                        <td>{{ $expire->getFinishString() }}</td>
                                        <td>
                                            <a class="btn btn-success btn-sm" href="{{ route('expired.edit', [$expire->id]) }}">Editar</a>
                                            {!! Form::open(['class'=>'form-inline', 'url'=>route('expired.destroy', $expire->id), 'method'=>'DELETE', 'style'=>'display:inline']) !!}
                                                <button class="btn btn-danger btn-sm" onclick="javascript:return confirm('¿Esta seguro de eliminar este evento expirado?')">Eliminar</button>
                                             {!! Form::close() !!}
                                         </td>
                                        
                                        
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
                    @if( sizeof($expired) > 0 && sizeof($expired) > 0 )
                    <nav aria-label="navigation" class="pagination-general">
                        {{ $expired->appends(request()->input())->links() }}
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