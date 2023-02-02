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
                <a class="btn btn-info  m-l-15" href="{{ route('team.create') }}">
                    <i class="fa fa-plus-circle"></i> Añadir Miembro
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
                    
                    <h4 class="card-title">Listado de Miembros del Equipo</h4>
                    <h6 class="card-subtitle mb-3">A continuación se muestran los miembros del equipo que se ubican en la sección "Quienes Somos".</h6>


                    <div class="table-responsive mt-1">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Imagen</th>
                                    <th>Nombre</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if( sizeof($members) > 0 && count($members) > 0)

                                    @foreach( $members as $member )

                                    <tr>
                                        <td><img src="{{ $member->image }}" height="50px"></td>
                                        <td>{{ $member->name }}</td>
                                        <td>
                                            <a class="btn btn-success btn-sm" href="{{ route('team.edit', [$member->id]) }}">Editar Miembro</a>
                                            {!! Form::open(['class'=>'form-inline', 'url'=>route('team.destroy', $member->id), 'method'=>'DELETE', 'style'=>'display:inline']) !!}
                                                <button class="btn btn-danger btn-sm" onclick="javascript:return confirm('¿Esta seguro de eliminar este miembro?')">Eliminar</button>
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
                    @if( sizeof($members) > 0 && sizeof($members) > 0 )
                    <nav aria-label="navigation" class="pagination-general">
                        {{ $members->appends(request()->input())->links() }}
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