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
                    <div class="row">
                        <div class="col-8">
                            <h6 class="card-subtitle mb-3">A continuación se muestran los pagos registrados en esta plataforma.</h6>
                        </div>
                        <div class="col-4">
                            <form method="POST" action="{{ route('payments.searchFolio') }}">
                                @csrf
                                <div class="mb-3">
                                    <div class="input-group mb-3">
                                        <input
                                            type="text"
                                            name="folio"
                                            class="form-control"
                                            placeholder="Buscar por folio"
                                            value="{{ old('folio') }}"
                                        >
                                        <div class="input-group-append">
                                            <button class="btn btn-info">Buscar</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-10">
                            <form method="GET" class="row">

                                <div class="col-2">
                                    <div class="mb-3">
                                        <select name="status" class="form-control">
                                            <option value="">Filtrar por</option>
                                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pendiente</option>
                                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Pagado</option>
                                        </select>
                                    </div>
                                </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <input
                                        type="text"
                                        name="search"
                                        class="form-control"
                                        placeholder="Pago o Inscrito."
                                        value="{{ request('search') }}"
                                    >
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="input-group mb-3">
                                    <select name="event" class="form-control">
                                        <option value="">Evento o Curso.</option>
                                        @foreach($events as $key => $value)
                                            <option value="{{ $key }}" {{ (string) $key === (string) $event ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button class="btn btn-info">Filtrar</button>
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>
                        <div class="col-2 text-right">
                            <button class="btn btn-info" id="tickets-emit">Emitir Boletas</button>
                        </div>
                    </div>


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
                                    <th>Emitir Boleta</th>
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
                                        <td>
                                            <div class="form-check {{ $payment->status != 'pagado' || !empty($payment->dte) ? 'disabled' : '' }}">
                                                <input
                                                    class="form-check"
                                                    {{ $payment->status != 'pagado' || !empty($payment->dte) ? 'disabled' : '' }}
                                                    type="checkbox"
                                                    name="payments[]"
                                                    value="{{ $payment->id }}"
                                                    id="payment-{{ $payment->id }}"
                                                >
                                                <label class="form-check {{ $payment->status != 'pagado' || !empty($payment->dte) ? 'disabled' : '' }} " for="payment-{{ $payment->id }}"></label>
                                            </div>
                                        </td>
                                        <td><label class="label label-{{ $payment->status=='pagado' && !empty($payment->dte) ? 'info' : 'primary' }}">{!! $payment->status=='pagado' && !empty($payment->dte) ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>' !!}</label> </td>
                                        <td> <label class="label label-{{ $payment->status=='pending' ? 'warning' : 'success' }}">{{ $payment->status=='pending' ? 'PENDIENTE' : strtoupper($payment->status) }}</label> </td>
                                        <td>
                                            <a class="btn btn-success btn-sm" href="{{ route('payments.show', [$payment->id]) }}">Ver Pago</a>

                                            @if( $payment->status == 'pending' )
                                                <form class="form-inline" action="{{ route('payments.destroy', $payment->id) }}" method="POST" style="display:inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-sm" onclick="javascript:return confirm('¿Esta seguro de eliminar este pago?')">Eliminar</button>
                                                </form>
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
        let payments_to_invoice = [];
        $('#mdate').bootstrapMaterialDatePicker({ weekStart: 1, time: false });

        // Capture the check of all checboxes payments for save the marks
        $('input[name="payments[]"]').on('change', function() {
            var payment = $(this).val();

            if( $(this).is(':checked') )
                payments_to_invoice.push(payment);
            else
                payments_to_invoice = payments_to_invoice.filter(function(item) {
                    return item != payment;
                });

            console.log(payments_to_invoice);
        });

        // Emitir Boletas
        $('button#tickets-emit').on('click', function() {
            console.log(payments_to_invoice);
            if( payments_to_invoice.length > 0 ) {
                $.ajax({
                    url: '{{ route('payments.processTickets') }}',
                    method: 'POST',
                    data: {
                        payments: payments_to_invoice,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if( response.status == 'success' ) {
                            alert('Se emitirán las boletas en los próximos minutos.');
                            location.reload();
                        }
                    }
                });
            } else {
                alert('Debe seleccionar al menos un pago para emitir boleta.');
            }
        });
    </script>
@endsection