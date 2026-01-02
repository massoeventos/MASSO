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
                    
                    <h4 class="card-title">{{ $title }}</h4>
                    <h6 class="card-subtitle mb-3">A continuación se muestran las respuestas de las encuestas en la plataforma.</h6>

                    <form method="GET" class="row">

                        <div class="col-12">
                            <div class="input-group mb-3">
                                <select name="search" class="form-control">
                                    <option value="">Seleccione evento para filtrar.</option>
                                    @foreach($events as $key => $value)
                                        <option value="{{ $key }}" {{ (string) $key === (string) request('search') ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button class="btn btn-info">Filtrar</button>
                                </div>
                            </div>
                        </div>

                    </form>

                    <div class="table-responsive mt-1">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Fecha Respuesta</th>
                                    <th>Evento</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if( sizeof($surveys) > 0 && count($surveys) > 0)

                                    @foreach( $surveys as $survey )

                                    <tr>
                                        <td>{{ $survey->rut }}</td>
                                        <td>{{ $survey->name }}</td>
                                        <td>{{ $survey->email }}</td>
                                        <td>
                                            <a class="btn btn-success btn-sm" href="{{ route('surveys.edit', [$survey->id]) }}">Editar Cliente</a>
                                            <form class="form-inline" action="{{ route('surveys.destroy', $survey->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm" onclick="javascript:return confirm('¿Esta seguro de eliminar este cliente?')">Eliminar</button>
                                             </form>
                                         </td>
                                        
                                        
                                    </tr>
                                    @endforeach

                                @else
                                    <tr>
                                        <td colspan=5>
                                            No se encontraron respuestas de encuesta.
                                        </td>
                                    </tr>
                                @endif
                                
                            </tbody>
                        </table>
                    </div>
                    @if( sizeof($surveys) > 0 && sizeof($surveys) > 0 )
                    <nav aria-label="navigation" class="pagination-general">
                        {{ $surveys->appends(request()->input())->links() }}
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