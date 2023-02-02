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
                    <li class="breadcrumb-item"><a href="{{ route('events.index') }}">Eventos</a></li>
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
                            <h4 class="card-title">Nuevo Evento</h4>
                        </div>
                        <div class="col-12">
                            <h6 class="card-subtitle">Favor llene todos los campos que aparecen a continuación.</h6>
                        </div>
                    </div>



                    {!! Form::open(['url'=>route('events.store'), 'method'=>'POST', 'files'=>true, 'class'=>'row']) !!}
                        <div class="col-md-9 left-wrapper loading-wrapper">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="m-t-20">Nombre Evento</label>
                                    {!! Form::text('name', null, ['class'=>'form-control', 'required'=>'required', 'placeholder'=>'Ej: Simposio de Salud' ]) !!}
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">Ubicación</label>
                                    {!! Form::text('location', null, ['class'=>'form-control', 'required'=>'required', 'placeholder'=>'Ej:  Clínica Alemana' ]) !!}
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Descripción General</label><br>
                                    {!! Form::textarea('description', null, ['class'=>'textarea_editor form-control' ]) !!}<br>
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Descripción General en Inglés</label><br>
                                    {!! Form::textarea('description_eng', null, ['class'=>'textarea_editor_eng form-control' ]) !!}<br>
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Tèrminos y condiciones</label><br>
                                    {!! Form::textarea('terms_and_conditions', null, ['class'=>'textarea_editor_tems form-control' ]) !!}<br>
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Tèrminos y condiciones en Inglés</label><br>
                                    {!! Form::textarea('terms_and_conditions_eng', null, ['class'=>'textarea_editor_tems_eng form-control' ]) !!}<br>
                                </div>

                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="m-t-20">Permitir Multiple Selecciòn</label><br>
                                            {!! Form::select('is_multiple_selection_ticket', [0=>'No', 1=>'Si'], 0, ['id'=>'is_multiple_selection_ticket','class'=>'form-control', 'required'=>'required']) !!}
                                        </div>
                                        <div class="col-md-6">
                                            <label class="m-t-20">Cantidad Maxima de Selecciòn</label><br>
                                            {!! Form::selectRange('max_selection_ticket', 1, 20, 1, ['id'=>'max_selection_ticket','class'=>'form-control', 'required'=>'required', 'disabled' => '' ]) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20 col-12">Entradas / Tickets</label>
                                    <small class="col-12" style="padding-bottom: 15px;display: block;text-align: justify;">* Puede ingresar los tipos de entrada disponibles. Puede especificar una fecha de inicio y término que es la ventana de tiempo que estará disponible dicha entrada. Si deja vacío estos campos, se entiende que no existirán restricciones.</small>

                                    @if( !empty(old('tickets')) )
                                    @foreach( old('tickets') as $key=>$ticket )
                                    <div class="row ticket-wrapper" data-num="0">
                                        <div class="col-6">
                                            <small>Entrada</small>
                                            <input type="text" class="form-control" name="tickets[{{ $key }}][name]" value="{{ $ticket['name'] }}" placeholder="Nombre entrada">
                                        </div>
                                        <div class="col-6">
                                            <small>Entrada Inglés</small>
                                            <input type="text" class="form-control" name="tickets[{{ $key }}][name_eng]" value="{{ $ticket['name_eng'] }}" placeholder="Nombre entrada inglés">
                                        </div>
                                        <div class="col-12">
                                            <small>Descripción</small>
                                            <input type="text" class="form-control" name="tickets[{{ $key }}][description]" value="{{ $ticket['description'] }}" placeholder="Descripción de la entrada">
                                        </div>
                                        <div class="col-12">
                                            <small>Descripción Inglés</small>
                                            <input type="text" class="form-control" name="tickets[{{ $key }}][description_eng]" value="{{ $ticket['description_eng'] }}" placeholder="Descripción de la entrada (inglés)">
                                        </div>
                                        <div class="col-3">
                                            <small>Precio</small>
                                            <input type="text" class="form-control currency" name="tickets[{{ $key }}][price]" value="{{ $ticket['price'] }}" placeholder="Valor entrada">
                                        </div>
                                        <div class="col-3">
                                            <small>Stock</small>
                                            <input type="number" class="form-control" name="tickets[{{ $key }}][stock]" value="{{ $ticket['stock'] }}" placeholder="Stock">
                                        </div>
                                        <div class="col-3">
                                            <small>Desde</small>
                                            <input type="date" class="form-control date" name="tickets[{{ $key }}][from]" value="{{ $ticket['from'] }}" placeholder="Desde">
                                        </div>
                                        <div class="col-3">
                                            <small>Hasta</small>
                                            <input type="date" class="form-control date" name="tickets[{{ $key }}][to]" value="{{ $ticket['to'] }}" placeholder="Hasta">
                                        </div>
                                        <div class="col-3">
                                            <small>Ticket Obligatorio</small>
                                            {!! Form::select(tickets[$key][is_mandatory], ['false'=>'No', 'true'=>'Si'], 0, ['id'=>'is_mandatory','class'=>'form-control ticket-is-mandatory', 'required'=>'required']) !!}
                                        </div>
                                        <small class="col-12 text-right trash"><span class="btn btn-xs btn-danger"> <i class="fa fa-trash"></i> Eliminar</span></small>
                                    </div>
                                    @endforeach
                                    @else
                                    <div class="row ticket-wrapper" data-num="0">
                                        <div class="col-6">
                                            <small>Entrada</small>
                                            <input type="text" class="form-control" name="tickets[0][name]" placeholder="Nombre entrada">
                                        </div>
                                        <div class="col-6">
                                            <small>Entrada Inglés</small>
                                            <input type="text" class="form-control" name="tickets[0][name_eng]" placeholder="Nombre entrada inglés">
                                        </div>
                                        <div class="col-12">
                                            <small>Descripción</small>
                                            <input type="text" class="form-control" name="tickets[0][description]" placeholder="Descripción de la entrada">
                                        </div>
                                        <div class="col-12">
                                            <small>Descripción Inglés</small>
                                            <input type="text" class="form-control" name="tickets[0][description_eng]" placeholder="Descripción de la entrada (inglés)">
                                        </div>
                                        <div class="col-3">
                                            <small>Precio</small>
                                            <input type="text" class="form-control currency" name="tickets[0][price]" placeholder="Valor entrada">
                                        </div>
                                        <div class="col-3">
                                            <small>Stock</small>
                                            <input type="number" class="form-control" name="tickets[0][stock]" placeholder="Stock">
                                        </div>
                                        <div class="col-3">
                                            <small>Desde</small>
                                            <input type="date" class="form-control date" name="tickets[0][from]" placeholder="Desde">
                                        </div>
                                        <div class="col-3">
                                            <small>Hasta</small>
                                            <input type="date" class="form-control date" name="tickets[0][to]" placeholder="Hasta">
                                        </div>
                                        <div class="col-3">
                                            <small>Ticket Obligatorio</small>
                                            {!! Form::select('tickets[0][is_mandatory]', ['false'=>'No', 'true'=>'Si'], 0, ['id'=>'is_mandatory','class'=>'form-control  ticket-is-mandatory', 'required'=>'required']) !!}
                                        </div>
                                        <small class="col-12 text-right trash"><span class="btn btn-xs btn-danger"> <i class="fa fa-trash"></i> Eliminar</span></small>
                                    </div>
                                    @endif

                                    <div class="col-12 mt-3 add-ticket text-right">
                                        <span class="btn btn-dark">Añadir Entrada</span>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20 col-12">Formulario de Registro</label>
                                    <small class="col-12" style="padding-bottom: 15px;display: block;text-align: justify;">* Ingrese los campos asociados al registro del evento.</small>

                                    @if( !empty(old('inputs')) )
                                    @foreach( old('inputs') as $key=>$input )
                                    <div class="row ticket-wrapper" data-num="0">
                                        <div class="col-6">
                                            <small>Nombre de Campo</small>
                                            <input type="text" class="form-control" name="inputs[{{ $key }}][name]" value="{{ $input['name'] }}" placeholder="Nombre de Campo">
                                        </div>
                                        <div class="col-6">
                                            <small>Nombre de Campo - Inglés</small>
                                            <input type="text" class="form-control" name="inputs[{{ $key }}][name_eng]" value="{{ $input['name_eng'] }}" placeholder="Nombre de Campo en Inglés">
                                        </div>
                                        <div class="col-3">
                                            <small>Tipo de Campo</small>
                                            <select  class="form-control" name="inputs[{{ $key }}][type]" placeholder="Seleccione si es un campo obligatorio"><option @if($input["type"]=='text') selected @endif value="text">Texto</option><option @if($input["type"] == 'file' ) selected @endif value="file">Archivo</option></select>
                                        </div>
                                        <div class="col-3">
                                            <small>¿Requerido?</small>
                                            <select  class="form-control" name="inputs[{{ $key }}][required]" placeholder="Seleccione si es un campo obligatorio"><option @if($input["required"]==1) selected @endif value="1">Sí, obligatorio</option><option @if($input["required"] == 0 ) selected @endif value="0">No, no es obligatorio</option></select>
                                        </div>

                                        <small class="col-12 text-right trash"><span class="btn btn-xs btn-danger"> <i class="fa fa-trash"></i> Eliminar</span></small>
                                    </div>
                                    @endforeach
                                    @else
                                    <div class="row ticket-wrapper" data-num="0">
                                        <div class="col-6">
                                            <small>Nombre de Campo</small>
                                            <input type="text" class="form-control" name="inputs[0][name]" value="" placeholder="Nombre de Campo">
                                        </div>
                                        <div class="col-6">
                                            <small>Nombre de Campo Inglés</small>
                                            <input type="text" class="form-control" name="inputs[0][name_eng]" value="" placeholder="Nombre de Campo en Inglés">
                                        </div>
                                        <div class="col-3">
                                            <small>Tipo de Campo</small>
                                            <select  class="form-control" name="inputs[0][type]" placeholder="Seleccione si es un campo obligatorio"><option value="text">Texto</option><option value="file">Archivo</option></select>
                                        </div>
                                        <div class="col-3">
                                            <small>¿Requerido?</small>
                                            <select  class="form-control" name="inputs[0][required]" placeholder="Seleccione si es un campo obligatorio"><option value="1">Sí, obligatorio</option><option value="0">No, no es obligatorio</option></select>
                                        </div>

                                        <small class="col-12 text-right trash"><span class="btn btn-xs btn-danger"> <i class="fa fa-trash"></i> Eliminar</span></small>
                                    </div>
                                    @endif

                                    <div class="col-12 mt-3 add-input text-right">
                                        <span class="btn btn-dark">Añadir Campo</span>
                                    </div>
                                </div>



                            </div>
                        </div>
                        <div class="col-md-3 right-wrapper loading-wrapper">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="m-t-20">Fecha de Inicio</label>
                                    {!! Form::date('date_init', null, ['class'=>'date form-control', 'required'=>'required', 'placeholder'=>'Ej: 2019-08-01' ]) !!}
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">Fecha de Término</label>
                                    {!! Form::date('date_finish', null, ['class'=>'date form-control', 'required', 'placeholder'=>'Ej: 2019-08-10' ]) !!}
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">Organiza</label>
                                    {!! Form::text('organize', null, ['class'=>'form-control', 'placeholder'=>'' ]) !!}
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">Es UC?</label>
                                    {!! Form::select('isUC', [0=>'No es UC', 1=>'Si es UC'], null, ['class'=>'form-control', 'required']) !!}
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">¿El evento es visible?</label>
                                    {!! Form::select('status', [0=>'No Visible', 1=>'Visible'], null, ['class'=>'form-control', 'required'=>'required', 'placeholder'=>'Seleccione si es visible.' ]) !!}
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Imágen General</label><br>
                                    {!! Form::file('photo', null, ['required'=>'required', 'class'=>'form-control' ]) !!}<br>
                                </div>

                                <div class="col-md-12 mt-4 text-center">
                                    <button class="btn btn-primary">Crear Evento</button>
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

       $('body').on('keyup', 'input.currency', function(){

          formatToInput( $(this) );

       });


       $('body').on('click', '.ticket-wrapper .trash span', function(){

            if( confirm('¿Esta seguro?') ){
            ele = $(this).parent().parent().remove();
            }

       });

       $('body').on('click', '.add-ticket .btn', function(){

            template = '<div class="row ticket-wrapper">\
                <div class="col-6">\
                    <small>Entrada</small>\
                    <input type="text" class="form-control" name="tickets[0][name]" placeholder="Nombre entrada">\
                </div>\
                <div class="col-6">\
                    <small>Entrada Inglés</small>\
                    <input type="text" class="form-control" name="tickets[0][name_eng]" placeholder="Nombre entrada Inglés">\
                </div>\
                <div class="col-12">\
                    <small>Descripción</small>\
                    <input type="text" class="form-control" name="tickets[0][description]" placeholder="Descripción de la entrada">\
                </div>\
                <div class="col-12">\
                    <small>Descripción Inglés</small>\
                    <input type="text" class="form-control" name="tickets[0][description_eng]" placeholder="Descripción de la entrada (inglés)">\
                </div>\
                <div class="col-3">\
                    <small>Precio</small>\
                    <input type="text" class="form-control currency" name="tickets[0][price]" placeholder="Valor entrada">\
                </div>\
                <div class="col-3">\
                    <small>Stock</small>\
                    <input type="number" class="form-control" name="tickets[0][stock]" placeholder="Stock">\
                </div>\
                <div class="col-3">\
                    <small>Desde</small>\
                    <input type="date" class="form-control date" name="tickets[0][from]" placeholder="Desde">\
                </div>\
                <div class="col-3">\
                    <small>Hasta</small>\
                    <input type="date" class="form-control date" name="tickets[0][to]" placeholder="Hasta">\
                </div>\
                <div class="col-3">\
                    <small>Ticket Obligatorio</small>\
                    <select  class="form-control  ticket-is-mandatory" name="tickets[0][is_mandatory]" placeholder="Seleccione si es un campo obligatorio">\
                        <option value="false" selected>No</option>\
                        <option value="true">Si</option>\
                    </select>\
                </div>\
                <small class="col-12 text-right trash"><span class="btn btn-xs btn-danger"> <i class="fa fa-trash"></i> Eliminar</span></small>\
            </div>';

            i = Date.now();

            template = template.replace(/0/g, i);

            $('.add-ticket').before(template);


        });

       $('body').on('click', '.add-input .btn', function(){

            template = '<div class="row ticket-wrapper">\
                <div class="col-6">\
                    <small>Nombre de Campo</small>\
                    <input type="text" class="form-control" name="inputs[0][name]" placeholder="Nombre de campo">\
                </div>\
                <div class="col-6">\
                    <small>Nombre de Campo</small>\
                    <input type="text" class="form-control" name="inputs[0][name_eng]" placeholder="Nombre de campo en Inglés">\
                </div>\
                <div class="col-3">\
                    <small>Tipo de Campo</small>\
                    <select  class="form-control" name="inputs[0][type]" placeholder="Seleccione tipo de campo" value="text"><option>Texto</option><option value="file">Archivo</option></select>\
                </div>\
                <div class="col-3">\
                    <small>¿Requerido?</small>\
                    <select  class="form-control" name="inputs[0][required]" placeholder="Seleccione si es un campo obligatorio"><option value="1">Sí, obligatorio</option><option value="false">No, no es obligatorio</option></select>\
                </div>\
                <small class="col-12 text-right trash"><span class="btn btn-xs btn-danger"> <i class="fa fa-trash"></i> Eliminar</span></small>\
            </div>';

            i = Date.now();
            template = template.replace(/0/g, i);
            $('.add-input').before(template);
        });


        $(document).ready(function(){
            $('select.early_bird_caption').on('change', function(){
                checkEarly();
            });

            $('.textarea_editor').wysihtml5();
            $('.textarea_editor_eng').wysihtml5();
            $('.textarea_editor_tems').wysihtml5();
            $('.textarea_editor_tems_eng').wysihtml5();

            $('#is_multiple_selection_ticket').on('change', function(){
                const input = $('#max_selection_ticket');
                if( $(this).val() === '0')
                    input.attr("disabled","true").val(1);
                else
                    input.removeAttr('disabled').val(2);
            });
        });
    </script>
@endsection
