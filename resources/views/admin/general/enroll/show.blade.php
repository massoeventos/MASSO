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
                
                <a href="{{ route('enrolls.index', $event->id) }}" class="btn btn-danger m-l-15">
                    <i class="fa fa-arrow-left"></i>  Volver
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
                        <div class="col-12">
                            <h4 class="card-title">
                                Ficha Asistente {{ $assistant->getName() }}
                                <small class="b-block">
                                    Inscripción: {{ $assistant->id }}
                                </small>
                            </h4>
                        </div>
                        <div class="col-12">
                            <h6 class="card-subtitle">A continuación se muestra la ficha del usaurio.</h6>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 left-wrapper loading-wrapper">
                            <div class="row">
                                <div class="col-md-12 ">
                                    <div class="alert alert-success">El usuario está asociado al evento: <b>{{ $event->name }}</b>. Su fecha de inscripción fue: {{ date('d-m-Y H:I', strtotime($assistant->created_at)) }}</div>
                                </div>

                                <div class="col-12" style="background-color: #f4f4f4;padding: 20px 20px;">
                                    <div class="row">
                                        <div class="col-12">
                                            <h5>INFORMACIÓN GENERAL</h5>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="m-t-20">Nombre Asistente</label>
                                            <span class="form-control">{{ $assistant->name }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="m-t-20">Apellido Asistente</label>
                                            <span class="form-control">{{ $assistant->lastname }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="m-t-20">RUT o Pasaporte</label>
                                            <span class="form-control">{{ $assistant->passport }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="m-t-20">Correo Asistente</label>
                                            <span class="form-control">{{ $assistant->email }}</span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="m-t-20">Ticket Asignado</label>
                                            <span class="form-control">{{ $assistant->ticket->name }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if( !empty($assistant->enrolldata) )
                                <div class="col-12">
                                    <hr>
                                </div>
                                <div class="col-12" style="background-color: #f4f4f4;padding: 20px 20px;">
                                    <div class="row">
                                        <div class="col-12">
                                            <h5>INFORMACIÓN DE INSCRIPCIÓN</h5>
                                        </div>
                                        @foreach( $assistant->enrolldata as $key => $data )
                                        <div class="col-md-4">
                                            <label class="m-t-20">{{ $key }}</label>
                                            <span class="form-control">{{ $data }}</span>
                                        </div>
                                        @endforeach
                                        
                                    </div>
                                </div>
                                @endif


                                @if( !empty($assistant->payment()->first()) )
                                <div class="col-12">
                                    <hr>
                                </div>
                                <div class="col-12" style="background-color: #f4f4f4;padding: 20px 20px;">
                                    <div class="row">
                                        <div class="col-12">
                                            <h5>INFORMACIÓN DE PAGO</h5>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="m-t-20">Folio</label>
                                            <span class="form-control">{{ $assistant->payment()->first()->id }}</span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="m-t-20">Fecha de Pago</label>
                                            <span class="form-control">{{ date('d-m-Y H:i', strtotime($assistant->payment()->first()->created_at)) }}</span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="m-t-20">Total Pago</label>
                                            <span class="form-control">{{ number_format($assistant->payment()->first()->amount,0,',','.') }}</span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="m-t-20">DTE</label>
                                            <span class="form-control">{{ $assistant->payment()->first()->dte }}</span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="m-t-20">Documento</label>
                                            <span class="form-control">{{ $assistant->payment()->first()->dte !='' ? route('payments.dte', $assistant->payment()->first()->id) : '' }}</span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="m-t-20">DTE</label>
                                            <span class="form-control">{{ $assistant->payment()->first()->managment }}</span>
                                        </div>

                                        @if( !empty($assistant->payment()->first()->transactions()->first()) )

                                        <div class="col-md-4">
                                            <label class="m-t-20">Tipo de Pago</label>
                                            <span class="form-control">{{ ($assistant->payment()->first()->transactions()->first()->payment_type == 'VN' ? 'Débito' : 'Crédito') }}</span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="m-t-20">Tarjeta</label>
                                            <span class="form-control">{{ $assistant->payment()->first()->transactions()->first()->card_number }}</span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="m-t-20">Cod. Autorización</label>
                                            <span class="form-control">{{ $assistant->payment()->first()->transactions()->first()->auth_code }}</span>
                                        </div>

                                        @endif
                                        
                                    </div>
                                </div>
                                @endif
                                

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('footer')
    <style type="text/css">
        .right-wrapper.loading-wrapper {
            overflow: hidden;
            padding: 0 15px;
            background-color: #f2f2f2;
        }
        .ticket-wrapper > div {
            padding: 0 5px;
        }
        .left-wrapper.loading-wrapper {
            padding-right: 35px;
        }
        .card-body {
            flex: 1 1 auto;
            padding: 40px;
        }
        .ticket-wrapper small {
            padding: 5px 4px 0px;
            display: block;
            text-transform: uppercase;
            font-size: 9px;
        }
        .row.ticket-wrapper {
            padding: 5px 10px 13px!important;
            background-color: #f7f7f7;
            margin: 0 0 5px!important;
        }
    </style>
@endsection