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
                
                @if( $authUser->canDo('g.admin.create') )
                    <a class="btn btn-info  m-l-15" href="{{ route('g.admin.create') }}">
                        <i class="fa fa-plus-circle"></i> Añadir Administrador
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
                    
                    <h4 class="card-title">Listado de Administradores</h4>
                    <h6 class="card-subtitle mb-3">A continuación se muestran los administradores con acceso a esta plataforma.</h6>

                    <form method="GET" class="row">

                        <div class="col-12">
                            <div class="input-group mb-3">
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar por nombre o correo.">
                                <div class="input-group-append">
                                    <button class="btn btn-info" type="submit">Filtrar</button>
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
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if( sizeof($users) > 0 && count($users) > 0)

                                    @foreach( $users as $user )

                                    <tr>
                                        <td>{{ $user->rut }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ implode(',', $user->roles()->pluck('name')->toArray()) }}</td>
                                        <td>
                                            <a class="btn btn-success btn-sm" href="{{ route('g.admin.edit', [$user->id]) }}">Editar Usuario</a>

                                            @if( $user->id != $authUser->id )
                                            <form class="form-inline" action="{{ route('g.admin.destroy', $user->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm" onclick="javascript:return confirm('¿Esta seguro de eliminar este administrador?')">Eliminar</button>
                                            </form>
                                            @endif   
                                         </td>
                                        
                                        
                                    </tr>
                                    @endforeach

                                @else
                                    <tr>
                                        <td colspan=4>
                                            No se encontraron inventarios disponibles.
                                        </td>
                                    </tr>
                                @endif
                                
                            </tbody>
                        </table>
                    </div>
                    @if( sizeof($users) > 0 && sizeof($users) > 0 )
                    <nav aria-label="navigation" class="pagination-general">
                        {{ $users->appends(request()->input())->links() }}
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