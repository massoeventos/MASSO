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
                    <li class="breadcrumb-item"><a href="{{ route('enrolls.index', $event->id) }}">{{ $event->name }}</a></li>
                    <li class="breadcrumb-item active">{{ $title }}</li>
                </ol>
                
                <a href="{{ route('events.index') }}" class="btn btn-danger m-l-15">
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
                            <h4 class="card-title">Nuevo Asistente Evento</h4>
                        </div>
                        <div class="col-12">
                            <h6 class="card-subtitle">Favor llene todos los campos que aparecen a continuación.</h6>
                        </div>
                    </div>
                    
                    
                    
                    {!! Form::open(['url'=>route('enrolls.store', $event->id), 'method'=>'POST', 'files'=>true, 'class'=>'row']) !!}
                        <div class="col-md-12 left-wrapper loading-wrapper">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="m-t-20">Nombre Asistente</label>
                                    {!! Form::text('name', null, ['class'=>'form-control', 'required'=>'required', 'placeholder'=>'Ej: Paola' ]) !!}
                                </div>
                                <div class="col-md-4">
                                    <label class="m-t-20">Apellido Asistente</label>
                                    {!! Form::text('lastname', null, ['class'=>'form-control', 'required'=>'required', 'placeholder'=>'Ej: Massó' ]) !!}
                                </div>
                                <div class="col-md-4">
                                    <label class="m-t-20">RUT o Pasaporte</label>
                                    {!! Form::text('passport', null, ['class'=>'form-control', 'required'=>'required', 'placeholder'=>'Ej:  11.111.111-1' ]) !!}
                                </div>

                                <div class="col-md-4">
                                    <label class="m-t-20">Correo Asistente</label>
                                    {!! Form::email('email', null, ['class'=>'form-control', 'required'=>'required', 'placeholder'=>'Ej: paola@massoeventos.cl' ]) !!}
                                </div>


                                <div class="col-md-4">
                                    <label class="m-t-20">Entrada</label>
                                    {!! Form::select('ticket_id', $event->tickets()->pluck('name','id'), null, ['class'=>'form-control', 'required'=>'required', 'placeholder'=>'Seleccione ticket.' ]) !!}
                                </div>

                                

                                    <div class="col-12 mt-3 add-ticket text-right">
                                        <button class="btn btn-dark">Añadir Asistente</button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    {!! Form::close() !!}
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
    <script type="text/javascript">
        $('.date').bootstrapMaterialDatePicker({ weekStart: 1, time: false });
        $('.textarea_editor').wysihtml5();

        var checkEarly = function(){
            value = $('select.early_bird_caption').val();
            
            if( value == 1 ){
                $('.early_bird_wrapper').fadeIn();
            }else{
                $('.early_bird_wrapper').fadeOut();
            }
        }
        var number_format = function(number,decimals,dec_point,thousands_sep) {
           number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
           var n = !isFinite(+number) ? 0 : +number,
               prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
               sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
               dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
               s = '',
               toFixedFix = function (n, prec) {
                   var k = Math.pow(10, prec);
                   return '' + Math.round(n * k) / k;
               };
           // Fix for IE parseFloat(0.55).toFixed(0) = 0;
           s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
           if (s[0].length > 3) {
               s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
           }
           if ((s[1] || '').length < prec) {
               s[1] = s[1] || '';
               s[1] += new Array(prec - s[1].length + 1).join('0');
           }
           return s.join(dec);
       }
        var formatToInput = function( ele ){

          number = ele.val();
          number = number.replace(/\D/g,'');
          number = number_format( number, 0, ',', '.' );
          ele.val( '$'+number );
       }

       

    </script>
@endsection