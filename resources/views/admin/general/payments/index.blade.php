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

                    <h4 class="card-title">Listado de Pagos</h4>
                    <h6 class="card-subtitle mb-3">A continuación se muestran los pagos registrados en esta plataforma.</h6>

                    {!! Form::open(['method'=>'GET', 'class'=>'row']) !!}

                        <div class="col-2">
                            <div class="mb-3">
                                {!! Form::select('status', ['Pendiente','Pagado'], null, ['class'=>'form-control', 'placeholder'=>'Filtrar por']) !!}
                            </div>
                        </div>
                    <div class="col-4">
                        <div class="mb-3">
                            {!! Form::text('search', null, ['class'=>'form-control', 'placeholder'=>'Pago o Inscrito.']) !!}
                        </div>
                    </div>
                        <div class="col-6">
                            <div class="input-group mb-3">
                                {!! Form::select('event', $events, $event, ['class'=>'form-control', 'placeholder'=>'Evento o Curso.']) !!}
                                <div class="input-group-append">
                                    <button class="btn btn-info">Filtrar</button>
                                </div>
                            </div>
                        </div>

                    {!! Form::close() !!}

                    <div class="table-responsive mt-1">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Fecha</th>
                                    <th width="25%">
                                        Cliente / Descripción
                                    </th>
                                    <th>Monto</th>
                                    <th>Boleta</th>
                                    <th>Estado</th>
                                    <th width="200px">Acción</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if( sizeof($payments) > 0 && count($payments) > 0)

                                    @foreach( $payments as $payment )

                                    <tr>
                                        <td>{{ $payment->id }}</td>
                                        <td>{{ date('d-m-Y H:i', strtotime($payment->updated_at)) }}</td>
                                        <td>
                                            {{ $payment->name }} {{ $payment->lastname }}<br>
                                            <small>{{ $payment->description }}</small>
                                        </td>
                                        <td>${{ number_format($payment->amount,0,',','.') }}<br>{{ $payment->getCanal() }}</td>
                                        <td><label class="label label-{{ $payment->status=='pagado' && !empty($payment->dte) ? 'info' : 'primary' }}">{!! $payment->status=='pagado' && !empty($payment->dte) ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>' !!}</label> </td>
                                        <td> <label class="label label-{{ $payment->status=='pending' ? 'warning' : 'success' }}">{{ $payment->status=='pending' ? 'PENDIENTE' : strtoupper($payment->status) }}</label> </td>
                                        <td>
                                            <a class="btn btn-success btn-sm" href="{{ route('payments.show', [$payment->id]) }}">Ver Pago</a>

                                            @if( $payment->status == 'pending' )
                                                {!! Form::open(['class'=>'form-inline', 'url'=>route('payments.destroy', $payment->id), 'method'=>'DELETE', 'style'=>'display:inline']) !!}
                                                    <button class="btn btn-danger btn-sm" onclick="javascript:return confirm('¿Esta seguro de eliminar este pago?')">Eliminar</button>
                                                 {!! Form::close() !!}
                                            @endif
                                         </td>


                                    </tr>
                                    @endforeach

                                @else
                                    <tr>
                                        <td colspan=4>
                                            No se encontraron pagos en la plataforma.
                                        </td>
                                    </tr>
                                @endif

                            </tbody>
                        </table>
                    </div>
                    @if( sizeof($payments) > 0 && sizeof($payments) > 0 )
                    <nav aria-label="navigation" class="pagination-general">
                        {{ $payments->appends(request()->input())->links() }}
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
