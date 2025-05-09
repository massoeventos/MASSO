@extends('layouts.panel')

@section('content')

    <style type="text/css">
        h5.card-title {
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-left: 3px;
        }
        .table .thead-dark th {
            color: #fff;
            background-color: #761A7D;
            border-color: #761A7D;
            text-transform: uppercase;
            padding: 10px;
        }
        div.card-body > div.row > div.col-md-12 > div.row > div {
            padding: 0px 30px;
        }
        span.form-control label {
            margin: 0!important;
        }
        div.loading-wrapper div.col-md-6 label {
            text-transform: uppercase;
            font-size: 11px;
            margin-bottom: 0;
            margin-top: 12px;
        }
        div.loading-wrapper span.form-control {
            font-size: 13px;
            line-height: 23px;
        }
    </style>
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h4 class="text-themecolor">{{ $title }}</h4>
        </div>
        <div class="col-md-7 align-self-center text-right">
            <div class="d-flex justify-content-end align-items-center">
                <ol class="breadcrumb d-none d-lg-flex">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Listado de Pagos</a></li>
                    <li class="breadcrumb-item active">{{ $title }}</li>
                </ol>

                <a href="{{ route('payments.index') }}" class="btn btn-danger m-l-15">
                    <i class="fa fa-arrow-left"></i>  Volver a Pagos
                </a>

            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-12 flash-container">
            @include('admin.common.flash')
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        @if( $payment->status == 'pending' )
                            <div class="col-12">
                                <div class="alert alert-danger">
                                    Este pago se encuentra pendiente.
                                </div>
                            </div>
                        @elseif( $payment->status == 'pagado' && empty($payment->dte))

                            <div class="col-12">
                                <div class="alert alert-info">
                                    Este pago se encuentra ratificado. No tiene asociado un DTE.
                                </div>
                            </div>
                        @elseif( $payment->status == 'pagado' && !empty($payment->dte))

                            <div class="col-12">
                                <div class="alert alert-success">
                                    Pago ratificado y boleta electrónica emitida. 
                                </div>
                            </div>


                        @endif
                    </div>


                    <div class="row">
                        <div class="col-md-12 loading-wrapper">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <h5 class="mt-2 card-title">Información del Pago</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="m-t-10">Fecha</label>
                                            <span class="form-control">{{ date('d-m-Y H:i', strtotime($payment->created_at)) }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="m-t-10">Estado del Pago</label>
                                            <span class="form-control"><label class="label label-{{ $payment->status=='pending' ? 'warning' : 'primary' }}">{{ $payment->status=='pending' ? 'PENDIENTE' : strtoupper($payment->status) }}</label></span>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="m-t-10">Monto del Pago</label>
                                            <span
                                                id="monto"
                                                class="form-control {{ $payment->status=='pending' ? 'allow-edit' : '' }}"
                                            >
                                                ${{ number_format($payment->amount,0,',','.') }}
                                            </span>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="m-t-10">Método de Pago</label>
                                            <span
                                                id="managment"
                                                class="form-control {{ $payment->status=='pending' ? 'allow-edit' : '' }}"
                                            >
                                                {{ $payment->getCanal() }}
                                            </span>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="m-t-10">Evento Asociado</label>
                                            <span class="form-control text-uppercase">
                                                <?php
                                                    $events = $payment->getEvent();
                                                    if(is_array($events) && count($events) > 0) {
                                                        echo '<ul>';
                                                        foreach ($events as $event){
                                                            echo "<li>{$event}</li>";
                                                        }
                                                        echo '</ul>';
                                                    } else {
                                                        echo '-';
                                                    }
                                                ?>
                                            </span>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="m-t-10">Cliente</label>
                                            <span class="form-control text-uppercase">{{ $payment->name }} {{ $payment->lastname }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="m-t-10">RUT</label>
                                            <span class="form-control text-uppercase">{{ $payment->rut_print }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="m-t-10">DNI/Pasaporte</label>
                                            <span class="form-control text-uppercase">{{ $payment->getPropertyData($payment->processData(), 'passport') }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="m-t-10">Correo Cliente</label>
                                            <span
                                                id="email"
                                                class="form-control text-uppercase {{ $payment->status=='pending' ? 'allow-edit' : '' }}"
                                            >
                                                {{ $payment->email }}
                                            </span>
                                        </div>

                                        @if ($payment->nationalityCountry)
                                            <div class="col-md-12">
                                                <label class="m-t-10">Nacionalidad</label>
                                                <span class="form-control text-uppercase">{{ $payment->nationalityCountry->name }}</span>
                                            </div>
                                        @endif

                                        @if ($payment->country_id)
                                            <div class="col-md-6">
                                                <label class="m-t-10">País de residencia</label>
                                                <span class="form-control text-uppercase">{{ $payment->country->name }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="m-t-10">Ciudad</label>
                                                <span class="form-control text-uppercase">{{ $payment->custom_city }}</span>
                                            </div>
                                        @else
                                            @if ($payment->city)
                                                <div class="col-md-4">
                                                    <label class="m-t-10">País</label>
                                                    <span class="form-control text-uppercase">{{ $payment->city->region->country->name }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="m-t-10">Región</label>
                                                    <span class="form-control text-uppercase">{{ $payment->city->region->name }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="m-t-10">Ciudad</label>
                                                    <span class="form-control text-uppercase">{{ $payment->city->name }}</span>
                                                </div>
                                            @endif
                                        @endif
                                        

                                        <div class="col-12">
                                            <label class="m-t-10">Descripción del Pago</label>
                                            <span class="form-control text-uppercase">{{ $payment->description }}</span>
                                        </div>

                                        <div class="col-12">
                                            <label class="m-t-10">Observación (Nota del cliente)</label>
                                            <span
                                                id="user_observation"
                                                class="form-control text-uppercase {{ $payment->status=='pending' ? 'allow-edit' : '' }}"
                                            >
                                                {{ $payment->user_observation }}
                                            </span>
                                        </div>


                                    </div>
                                </div>

                                @if( $payment->status == 'pending' )
                                    <div class="col-md-6 mb-3">
                                        <style type="text/css">
                                            .wrapper-gray {background-color: #f3f3f3;padding: 10px 25px 60px;}
                                        </style>
                                        <div class="wrapper-gray">
                                            <h5 class="mt-2 card-title">¿Confirmar Pago?</h5>

                                            <p>El pago aún está pendinte. ¿Desea confirmar este pago? Para proceder haga clic en el botón que aparece a continuación.</p>
                                            {!! Form::open(['class'=>'row', 'url'=>route('payments.ticket', $payment->id), 'method'=>'POST']) !!}
                                                <div class="col-12 text-right">
                                                    <input type="hidden" name="pending_pay" value="1">
                                                    <button class="btn btn-dark btn-sm" onclick="javascript:return confirm('¿Esta seguro de confirmar este pago?')">Confirmar Pago</button>
                                                </div>

                                             {!! Form::close() !!}
                                        </div>

                                    </div>
                                @else
                                    <div class="col-md-6 mb-3">
                                        <h5 class="mt-2 card-title">Información de la boleta</h5>


                                        @if( !empty($payment->dte) )
                                            <div class="col-12">
                                                <p>La boleta ya ha sido emitida para este pago. Puede descargar el documento haciendo clic en el siguiente botón.</p>
                                            </div>
                                            <div class="col-12 text-right">
                                                <a href="{{ route('payments.dte', $payment->id) }}" class="btn btn-success"><i class="fa fa-pdf"></i> Descargar {{ $payment->dte }}</a>
                                            </div>
                                        @else
                                            {!! Form::open(['class'=>'row', 'url'=>route('payments.ticket', $payment->id), 'method'=>'POST']) !!}
                                                <div class="col-12">
                                                    <label class="m-t-10 align-left text-left">Glosa de la Boleta</label>
                                                    <input type="text" class="form-control" required='required' value="{{ $payment->description }}" name="description">
                                                </div>
                                                <div class="col-12">
                                                    <label class="m-t-10 align-left text-left">Referencia del Pago (Opcional)</label>
                                                    <input type="text" class="form-control" value="" name="reference">
                                                    <small>Texto que aparece en la parte inferior de la boleta.</small>
                                                </div>
                                                <div class="col-12 text-right">
                                                    <button class="btn btn-dark btn-sm" onclick="javascript:return confirm('¿Esta seguro de emitir una boleta para este pago?')">Emitir Boleta</button>
                                                </div>

                                             {!! Form::close() !!}
                                        @endif
                                    </div>
                                @endif

                                <div class="col-md-6">
                                    <label class="m-t-10">Método de facturación</label>
                                    <span class="form-control text-uppercase">{{ $payment->billing_method_print }}</span>
                                </div>

                                
                                @if ($payment->invoice_data)
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-12">
                                                <h5 class="mt-3 card-title">Datos de facturación</h5>
                                            </div>
        
                                            <div class="col-md-6">
                                                <label class="m-t-10">Nombre o Razón Social</label>
                                                <span class="form-control text-uppercase">{{ $payment->invoice_data['business_name'] }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="m-t-10">RUT</label>
                                                <span class="form-control text-uppercase">{{ $payment->invoice_rut_print }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="m-t-10">Giro</label>
                                                <span class="form-control text-uppercase">{{ $payment->invoice_data['business_activity'] }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="m-t-10">Dirección</label>
                                                <span class="form-control text-uppercase">{{ $payment->invoice_data['address'] }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="m-t-10">Ciudad</label>
                                                <span class="form-control text-uppercase">{{ $payment->invoice_data['city'] }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="m-t-10">Teléfono</label>
                                                <span class="form-control text-uppercase">{{ $payment->invoice_data['phone'] }}</span>
                                            </div>
                                            <div class="col-12">
                                                <label class="m-t-10">Notas</label>
                                                <span class="form-control text-uppercase">{{ $payment->invoice_data['note'] }}</span>
                                            </div>
                                
                                        </div>
                                    </div>
                                @endif
                                    
                                
                                <div class="col-md-12 mt-3" style="padding-bottom: 40px;">
                                    <hr>
                                    <h5 class="card-title mt-3" style="padding: 10px 0px;">Resultados de Transacciones</h5>
                                    <div class="row">
                                        <div class="col-12">
                                            <table class="table table-striped table-hover">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Monto</th>
                                                        <th>Cód Auth</th>
                                                        <th>Resultado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    @foreach( $payment->transactions as $transaction)
                                                    <tr>
                                                        <td>{{ date('d-m-Y H:i', strtotime($transaction->created_at) ) }}</td>
                                                        <td>${{ number_format($transaction->amount,0,',','.') }}</td>
                                                        <td>{{ $transaction->auth_code }}</td>
                                                        <td>{{ $transaction->getStatus() }}</td>
                                                    </tr>

                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for edit data -->
    <div class="modal fade" tabindex="-1" aria-hidden="true" id="modalEdit">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar dato</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{route('payments.updateValue', $payment->id)}}" method="post">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="input-edit" id="label-input"></label>
                            <select name="value" id="select-edit" class="form-control" required>
                                <option value="webpay">WebPay</option>
                                <option value="transfer2">Transferencia</option>
                            </select>
                            <input type="text" name="value" class="form-control" id="input-edit" required>
                            <input type="hidden" name="field" class="form-control" id="input-field" required>
                            <input type="hidden" name="id" class="form-control" value="{{ $payment->id }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection

@section('footer')
    <script>
        $(document).ready(function () {
            $('.allow-edit').on('click', function(e) {
                const element_id = $(this).attr('id');
                $('input#input-edit').show();
                $('select#select-edit').hide();

                let value;
                let label;
                let field;

                if (element_id === 'monto') {
                    label = 'Monto del pago';
                    value = '{{ $payment->amount }}';
                    field = 'amount';
                }
                
                if (element_id === 'email') {
                    label = 'Correo Cliente';
                    value = '{{ $payment->email }}';
                    field = 'email';
                }

                if (element_id === 'user_observation') {
                    label = 'Observación (Nota del cliente)';
                    value = '{{ $payment->user_observation }}';
                    field = 'user_observation';
                }

                if (element_id === 'managment') {
                    console.log('{{ $payment->managment }}')
                    $('select#select-edit').show().val('{{ $payment->managment }}');
                    $('input#input-edit').hide();
                    label = 'Método de Pago';
                    value = '{{ $payment->managment }}';
                    field = 'managment';
                }


                $('#label-input').text(label);
                $('#input-edit').val(value);
                $('#input-field').val(field);

                // Show modal
                $('#modalEdit').modal('show');

            });

            $('select#select-edit').on('change', function(e) {
                const value = $(this).val();

                $('#input-edit').val(value);
            });
        })
    </script>
@endsection
