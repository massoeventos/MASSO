@extends('layouts.panel')

@section('content')

    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h4 class="text-themecolor">{{ $title }}</h4>
        </div>
        <div class="col-md-7 align-self-center text-right">
            <div class="d-flex justify-content-end align-items-center">
                <ol class="breadcrumb">
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
                    <h6 class="card-subtitle mb-3">A continuación se muestran los administradores con acceso a esta plataforma.</h6>

                    <form method="GET" class="row auto-filter">
                        <div class="col-md-3">
                            <select name="areas" class="form-control">
                                <option value="">Todas las Áreas</option>
                                @foreach($areas as $key => $label)
                                    <option value="{{ $key }}" {{ request('areas') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="module" class="form-control">
                                <option value="">Todos los Módulos</option>
                                @foreach($modules as $key => $label)
                                    <option value="{{ $key }}" {{ request('module') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="users" class="form-control">
                                <option value="">Todos los Usuarios</option>
                                @foreach($users as $key => $label)
                                    <option value="{{ $key }}" {{ request('users') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Filtrar por acción.">
                        </div>                        

                        <div class="hide">
                            <button type="submit">Enviar</button>
                        </div>

                    </form>

                    <div class="table-responsive mt-1">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Área</th>
                                    <th>Módulo</th>
                                    <th>Usuario</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if( sizeof($logs) > 0 && count($logs) > 0)

                                    @foreach( $logs as $log )

                                    <tr>
                                        <td>{{ $log->created_at->format('d-m-Y H:i') }}</td>
                                        <td>{{ $log->area }}</td>
                                        <td>{{ $log->module }}</td>
                                        <td>{{ !empty($log->user) ? $log->user->name : '' }}</td>
                                        <td>{{ $log->action }}</td>
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
                    @if( sizeof($logs) > 0 && sizeof($logs) > 0 )
                    <nav aria-label="navigation" class="pagination-general">
                        {{ $logs->appends(request()->input())->links() }}
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

        $(document).ready(function(){

            $('.auto-filter input, .auto-filter select').on('change', function(){
                $('.auto-filter').submit();
            });

        });
    </script>
@endsection