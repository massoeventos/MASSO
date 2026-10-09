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

                    <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="row">
                        @csrf
                        <div class="col-md-9 left-wrapper loading-wrapper">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="m-t-20">Nombre Evento *</label>
                                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required placeholder="Ej: Simposio de Salud">
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">Nombre Evento en Inglés</label>
                                    <input type="text" name="name_eng" value="{{ old('name_eng') }}" class="form-control" placeholder="Ej: Health Symposium (si se deja vacío, se muestra el nombre en español)">
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">Ubicación *</label>
                                    <input type="text" name="location" value="{{ old('location') }}" class="form-control" required placeholder="Ej:  Clínica Alemana">
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Banner principal</label><br>
                                    <input type="file" name="banner_image" class="form-control">
                                    <small class="text-muted">Imagen ancha que se mostrará justo antes de la descripción.</small>
                                </div>

                                
                                <div class="col-md-12">
                                    <label class="m-t-20">Descripción General</label><br>
                                    <textarea name="description" class="textarea_editor form-control">{{ old('description') }}</textarea><br>
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Fotos de pie de página</label><br>
                                    <input type="file" name="footer_images[]" class="form-control" multiple>
                                    <small class="text-muted">Opcional. Se mostrarán centradas después de la descripción.</small><br>
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Descripción General en Inglés</label><br>
                                    <textarea name="description_eng" class="textarea_editor_eng form-control">{{ old('description_eng') }}</textarea><br>
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Tèrminos y condiciones</label><br>
                                    <textarea name="terms_and_conditions" class="textarea_editor_tems form-control">{{ old('terms_and_conditions') }}</textarea><br>
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Tèrminos y condiciones en Inglés</label><br>
                                    <textarea name="terms_and_conditions_eng" class="textarea_editor_tems_eng form-control">{{ old('terms_and_conditions_eng') }}</textarea><br>
                                </div>

                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="m-t-20">Permitir Multiple Selecciòn *</label><br>
                                            <select name="is_multiple_selection_ticket" id="is_multiple_selection_ticket" class="form-control" required>
                                                <option value="0" {{ old('is_multiple_selection_ticket', 0) == 0 ? 'selected' : '' }}>No</option>
                                                <option value="1" {{ old('is_multiple_selection_ticket') == 1 ? 'selected' : '' }}>Si</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="m-t-20">Cantidad Maxima de Selecciòn *</label><br>
                                            <select name="max_selection_ticket" id="max_selection_ticket" class="form-control" required disabled>
                                                @for ($i = 1; $i <= 20; $i++)
                                                    <option value="{{ $i }}" {{ old('max_selection_ticket', 1) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Categorías y entradas: componente Vue (resources/js/admin/events/TicketsEditor.vue);
                                     datos iniciales en EventController::ticketsEditorData() --}}
                                <script type="application/json" id="tickets-editor-data">@json($ticketsEditor)</script>
                                <div id="tickets-editor" style="display: contents">
                                    <div class="col-md-12 text-muted m-t-20">Cargando entradas…</div>
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20 col-12">Formulario de Registro</label>
                                    <small class="col-12" style="padding-bottom: 15px;display: block;text-align: justify;">* Ingrese los campos asociados al registro del evento.</small>

                                    @if( !empty(old('inputs')) )
                                    @foreach( old('inputs') as $key=>$input )
                                    <div class="row ticket-wrapper" data-num="0">
                                        <div class="col-6">
                                            <small>Nombre de Campo</small>
                                            <input type="text" class="form-control" name="inputs[{{ $key }}][name]" value="{{ $input['name'] }}" placeholder="Nombre de Campo" required>
                                        </div>
                                        <div class="col-6">
                                            <small>Nombre de Campo - Inglés</small>
                                            <input type="text" class="form-control" name="inputs[{{ $key }}][name_eng]" value="{{ $input['name_eng'] }}" placeholder="Nombre de Campo en Inglés" required>
                                        </div>
                                        <div class="col-3">
                                            <small>Tipo de Campo</small>
                                            <select  class="form-control" name="inputs[{{ $key }}][type]" placeholder="Seleccione si es un campo obligatorio" required><option @if($input["type"]=='text') selected @endif value="text">Texto</option><option @if($input["type"] == 'file' ) selected @endif value="file">Archivo</option></select>
                                        </div>
                                        <div class="col-3">
                                            <small>¿Requerido?</small>
                                            <select  class="form-control" name="inputs[{{ $key }}][required]" placeholder="Seleccione si es un campo obligatorio" required><option @if($input["required"]==1) selected @endif value="1">Sí, obligatorio</option><option @if($input["required"] == 0 ) selected @endif value="0">No, no es obligatorio</option></select>
                                        </div>

                                        <small class="col-12 text-right trash"><span class="btn btn-xs btn-danger"> <i class="fa fa-trash"></i> Eliminar</span></small>
                                    </div>
                                    @endforeach
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
                                    <label class="m-t-20">Fecha de Inicio *</label>
                                    <input
                                        type="date"
                                        name="date_init"
                                        class="date form-control"
                                        required
                                        placeholder="Ej: 2019-08-01"
                                        value="{{ old('date_init') }}"
                                    >
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">Fecha de Término *</label>
                                    <input
                                        type="date"
                                        name="date_finish"
                                        class="date form-control"
                                        required
                                        placeholder="Ej: 2019-08-10"
                                        value="{{ old('date_finish') }}"
                                    >
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">Organiza</label>
                                    <input type="text" name="organize" value="{{ old('organize') }}" class="form-control" placeholder="">
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">Es UC?</label>
                                    <select name="isUC" class="form-control" required>
                                        <option value="0" {{ old('isUC') == 0 ? 'selected' : '' }}>No es UC</option>
                                        <option value="1" {{ old('isUC') == 1 ? 'selected' : '' }}>Si es UC</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">¿El evento es visible? *</label>
                                    <select name="status" class="form-control" required>
                                        <option value="" disabled {{ old('status') === null ? 'selected' : '' }}>Seleccione si es visible.</option>
                                        <option value="0" {{ old('status') === '0' || old('status') === 0 ? 'selected' : '' }}>No Visible</option>
                                        <option value="1" {{ old('status') === '1' || old('status') === 1 ? 'selected' : '' }}>Visible</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">¿Mostrar campos de ubicación? *</label>
                                    <select name="show_location_fields" class="form-control" required>
                                        <option value="" disabled {{ old('show_location_fields') === null ? 'selected' : '' }}>Seleccione si desea mostrar País, Región y Ciudad.</option>
                                        <option value="0" {{ old('show_location_fields') === '0' || old('show_location_fields') === 0 ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ old('show_location_fields') === '1' || old('show_location_fields') === 1 ? 'selected' : '' }}>Si</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">Imagen principal del evento *</label>
                                    <input type="file" name="photo" class="form-control" required>
                                    <small class="text-muted">Imagen principal del evento</small>
                                    <div class="image-block">
                                        <label class="m-t-10">¿Permitir pago por transferencia? *</label>
                                        <select name="allow_bank_transfer" class="form-control" required>
                                            <option value="" disabled {{ old('allow_bank_transfer') === null ? 'selected' : '' }}>Seleccione si permite pago por transferencia.</option>
                                            <option value="1" {{ old('allow_bank_transfer', 1) == 1 ? 'selected' : '' }}>Si</option>
                                            <option value="0" {{ old('allow_bank_transfer') == 0 ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <button class="btn btn-primary">Crear Evento</button>
                                </div>
                            </div>
                        </div>
                    </form>
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
        .image-block {
            margin: 10px 0 5px;
            background: #f7f7f7;
            border: 1px solid #ececec;
            border-radius: 6px;
            padding: 10px;
        }
        .media-preview {
            max-width: 100%;
            height: auto;
            display: block;
            border-radius: 4px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            text-align: center;
            color: #999;
            font-size: 12px;
        }
        .footer-preview {
            margin-top: 10px;
            text-align: center;
        }
        .footer-thumb {
            max-width: 180px;
            width: 100%;
            height: auto;
            display: inline-block;
            margin: 6px;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
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
       $('body').on('click', '.ticket-wrapper .trash span', function(){

            if( confirm('¿Esta seguro?') ){
            ele = $(this).parent().parent().remove();
            }

       });

       $('body').on('click', '.add-input .btn', function(){

            template = '<div class="row ticket-wrapper">\
                <div class="col-6">\
                    <small>Nombre de Campo</small>\
                    <input type="text" class="form-control" name="inputs[0][name]" placeholder="Nombre de campo" required>\
                </div>\
                <div class="col-6">\
                    <small>Nombre de Campo</small>\
                    <input type="text" class="form-control" name="inputs[0][name_eng]" placeholder="Nombre de campo en Inglés" required>\
                </div>\
                <div class="col-3">\
                    <small>Tipo de Campo</small>\
                    <select  class="form-control" name="inputs[0][type]" placeholder="Seleccione tipo de campo" required><option value="text">Texto</option><option value="file">Archivo</option></select>\
                </div>\
                <div class="col-3">\
                    <small>¿Requerido?</small>\
                    <select  class="form-control" name="inputs[0][required]" placeholder="Seleccione si es un campo obligatorio" required><option value="1">Sí, obligatorio</option><option value="false">No, no es obligatorio</option></select>\
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
    @include('common.vite-entry', ['entry' => 'resources/js/admin/events.js'])
@endsection
