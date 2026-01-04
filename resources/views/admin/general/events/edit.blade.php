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

                    <form method="POST" action="{{ route('events.update', $event->id) }}" enctype="multipart/form-data" class="row">
                        @csrf
                        <div class="col-md-9 left-wrapper loading-wrapper">
                            <div class="row">

                                <div class="col-md-12">
                                    <label class="m-t-20">Nombre Evento *</label>
                                    <input type="text" name="name" value="{{ old('name', $event->name) }}" class="form-control" required placeholder="Ej: Simposio de Salud">
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">Ubicación *</label>
                                    <input type="text" name="location" value="{{ old('location', $event->location) }}" class="form-control" required placeholder="Ej:  Clínica Alemana">
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="m-t-20">Banner principal</label><br>
                                    <input type="file" name="banner_image" class="form-control">
                                    <small class="d-block mb-1 text-muted">Imagen ancha que se mostrará justo antes de la descripción.</small>
                                    
                                    @if($event->bannerImage())
                                        <div class="image-block removable">
                                            <input type="checkbox" id="remove_banner" name="remove_banner" value="1" class="remove-toggle">
                                            <label for="remove_banner" class="remove-badge">×</label>
                                            <img src="{{ $event->bannerImage()->path }}" class="img-responsive media-preview" alt="Banner principal">
                                        </div>
                                        <small class="text-muted">Cargar uno nuevo reemplaza el actual.</small><br>
                                    @endif
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Descripción General</label><br>
                                    <textarea name="description" class="textarea_editor form-control">{{ old('description', $event->description) }}</textarea><br>
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Fotos de pie de página</label><br>
                                    <input type="file" name="footer_images[]" class="form-control" multiple>
                                    @if($event->footerImages()->count())
                                        <div class="footer-preview text-center">
                                            @foreach($event->footerImages() as $img)
                                                <div class="footer-item">
                                                    <input type="checkbox" id="remove_footer_{{ $img->id }}" name="remove_footer_ids[]" value="{{ $img->id }}" class="remove-toggle">
                                                    <label for="remove_footer_{{ $img->id }}" class="remove-badge">×</label>
                                                    <img src="{{ $img->path }}" class="img-responsive footer-thumb" alt="Footer image">
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    <small class="text-muted">Opcional. Se mostrarán centradas al final. Las nuevas se agregan al final.</small><br>
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Descripción General en Inglés</label><br>
                                    <textarea name="description_eng" class="textarea_editor_eng form-control">{{ old('description_eng', $event->description_eng) }}</textarea><br>
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Tèrminos y condiciones</label><br>
                                    <textarea name="terms_and_conditions" class="textarea_editor_tems form-control">{{ old('terms_and_conditions', $event->terms_and_conditions) }}</textarea><br>
                                </div>

                                <div class="col-md-12">
                                    <label class="m-t-20">Tèrminos y condiciones en Inglés</label><br>
                                    <textarea name="terms_and_conditions_eng" class="textarea_editor_tems_eng form-control">{{ old('terms_and_conditions_eng', $event->terms_and_conditions_eng) }}</textarea><br>
                                </div>

                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="m-t-20">Permitir Multiple Selecciòn *</label><br>
                                            <select name="is_multiple_selection_ticket" id="is_multiple_selection_ticket" class="form-control" required>
                                                <option value="0" {{ old('is_multiple_selection_ticket', $event->is_multiple_selection_ticket) == 0 ? 'selected' : '' }}>No</option>
                                                <option value="1" {{ old('is_multiple_selection_ticket', $event->is_multiple_selection_ticket) == 1 ? 'selected' : '' }}>Si</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="m-t-20">Cantidad Maxima de Selecciòn *</label><br>
                                            <select name="max_selection_ticket" id="max_selection_ticket" class="form-control" required>
                                                @for ($i = 1; $i <= 20; $i++)
                                                    <option value="{{ $i }}" {{ old('max_selection_ticket', $event->max_selection_ticket) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                @endfor
                                            </select>
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
                                            <input type="text" class="form-control" name="tickets[{{ $key }}][name]" value="{{ $ticket['name'] }}" placeholder="Nombre entrada" required>
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
                                            <input type="text" class="form-control currency" name="tickets[{{ $key }}][price]" value="{{ $ticket['price'] }}" placeholder="Valor entrada" required>
                                        </div>
                                        <div class="col-3">
                                            <small>Stock</small>
                                            <input type="number" class="form-control" name="tickets[{{ $key }}][stock]" value="{{ $ticket['stock'] }}" placeholder="Stock" required>
                                        </div>
                                        <div class="col-3">
                                            <small>Desde</small>
                                            <input type="date" class="form-control date" name="tickets[{{ $key }}][from]" value="{{ !((bool)strtotime($ticket['from'])) ? date('Y-m-d') : $ticket['from'] }}" placeholder="Desde" required>
                                        </div>
                                        <div class="col-3">
                                            <small>Hasta</small>
                                            <input type="date" class="form-control date" name="tickets[{{ $key }}][to]" value="{{ !((bool)strtotime($ticket['to'])) ? date('Y-m-d') : $ticket['to'] }}" placeholder="Hasta" required>
                                        </div>
                                        @php $isMandatory = isset($ticket['is_mandatory']) ? $ticket['is_mandatory'] : 'false'; @endphp
                                        <div class="col-3">
                                            <small>Ticket Obligatorio</small>
                                            <select name="tickets[{{ $key }}][is_mandatory]" class="form-control ticket-is-mandatory" id="is_mandatory" required>
                                                <option value="false" {{ ($isMandatory === 'false' || $isMandatory === 0 || $isMandatory === '0') ? 'selected' : '' }}>No</option>
                                                <option value="true" {{ ($isMandatory === 'true' || $isMandatory === 1 || $isMandatory === '1') ? 'selected' : '' }}>Si</option>
                                            </select>
                                        </div>
                                        <div class="col-3">
                                            <small>Requiere Documento</small>
                                            <select class="form-control ticket-requires-document" name="tickets[{{ $key }}][requires_document]" required="required">
                                                <option value="false" {{ (!isset($ticket['requires_document']) || $ticket['requires_document'] === 'false' || $ticket['requires_document'] === 0 || $ticket['requires_document'] === '0') ? 'selected' : '' }}>No</option>
                                                <option value="true" {{ (isset($ticket['requires_document']) && ($ticket['requires_document'] === 'true' || $ticket['requires_document'] === 1 || $ticket['requires_document'] === '1')) ? 'selected' : '' }}>Si</option>
                                            </select>
                                        </div>
                                        <small class="col-12 text-right trash"><span class="btn btn-xs btn-danger"> <i class="fa fa-trash"></i> Eliminar</span></small>
                                    </div>
                                    @endforeach
                                    @elseif( $event->tickets()->count() > 0 )
                                    @foreach( $event->tickets as $ticket )
                                    <?php $key = $ticket->id ?>
                                    <div class="row ticket-wrapper" data-num="0">
                                        <div class="col-6">
                                            <small>Nombre</small>
                                            <input type="text" class="form-control" name="tickets[{{ $key }}][name]" value="{{ $ticket['name'] }}" placeholder="Nombre entrada" required>
                                        </div>
                                        <div class="col-6">
                                            <small>Nombre Inglés</small>
                                            <input type="text" class="form-control" name="tickets[{{ $key }}][name_eng]" value="{{ $ticket['name_eng'] }}" placeholder="Nombre entrada">
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
                                            <input type="text" class="form-control currency" name="tickets[{{ $key }}][price]" value="{{ $ticket['price'] }}" placeholder="Valor entrada" required>
                                        </div>
                                        <div class="col-3">
                                            <small>Stock</small>
                                            <input type="number" class="form-control" name="tickets[{{ $key }}][stock]" value="{{ $ticket['stock'] }}" placeholder="Stock" required>
                                        </div>
                                        <div class="col-3">
                                            <small>Desde</small>
                                            <input type="date" class="form-control date" name="tickets[{{ $key }}][from]" value="{{ !((bool)strtotime($ticket['from'])) ? date('Y-m-d') : $ticket['from'] }}" placeholder="Desde" required>
                                        </div>
                                        <div class="col-3">
                                            <small>Hasta</small>
                                            <input type="date" class="form-control date" name="tickets[{{ $key }}][to]" value="{{ !((bool)strtotime($ticket['to'])) ? date('Y-m-d') : $ticket['to'] }}" placeholder="Hasta" required>
                                        </div>
                                        <div class="col-3">
                                            <small>Ticket Obligatorio</small>
                                            <select name="tickets[{{ $key }}][is_mandatory]" class="form-control  ticket-is-mandatory" id="is_mandatory" required>
                                                <option value="false" {{ $ticket->getIsMandatoryBooleanAttribute() ? '' : 'selected' }}>No</option>
                                                <option value="true" {{ $ticket->getIsMandatoryBooleanAttribute() ? 'selected' : '' }}>Si</option>
                                            </select>
                                        </div>
                                        <div class="col-3">
                                            <small>Requiere Documento</small>
                                            <select name="tickets[{{ $key }}][requires_document]" class="form-control ticket-requires-document" required>
                                                <option value="false" {{ $ticket->getRequiresDocumentBooleanAttribute() ? '' : 'selected' }}>No</option>
                                                <option value="true" {{ $ticket->getRequiresDocumentBooleanAttribute() ? 'selected' : '' }}>Si</option>
                                            </select>
                                        </div>
                                        <small class="col-12 text-right trash"><span class="btn btn-xs btn-danger"> <i class="fa fa-trash"></i> Eliminar</span></small>
                                    </div>
                                    @endforeach
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
                                    @elseif( $event->inputs()->count() > 0 )
                                    @foreach( $event->inputs as $input )
                                    <?php $key = $input->id ?>
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
                                    <input type="date" name="date_init" value="{{ old('date_init', date('Y-m-d', strtotime($event->date_init))) }}" class="date form-control" required placeholder="Ej: 2019-08-01">
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">Fecha de Término *</label>
                                    <input type="date" name="date_finish" value="{{ old('date_finish', date('Y-m-d', strtotime($event->date_finish))) }}" class="date form-control" required placeholder="Ej: 2019-08-10">
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">Organiza</label>
                                    <input type="text" name="organize" value="{{ old('organize', $event->organize) }}" class="form-control" placeholder="">
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">Es UC?</label>
                                    <select name="isUC" class="form-control" required>
                                        <option value="0" {{ old('isUC', $event->isUC) == 0 ? 'selected' : '' }}>No es UC</option>
                                        <option value="1" {{ old('isUC', $event->isUC) == 1 ? 'selected' : '' }}>Si es UC</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">¿El evento es visible? *</label>
                                    <select name="status" class="form-control" required>
                                        <option value="" disabled {{ old('status', $event->status) === null ? 'selected' : '' }}>Seleccione si es visible.</option>
                                        <option value="0" {{ old('status', $event->status) == 0 ? 'selected' : '' }}>No Visible</option>
                                        <option value="1" {{ old('status', $event->status) == 1 ? 'selected' : '' }}>Visible</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">¿Mostrar campos de ubicación? *</label>
                                    <select name="show_location_fields" class="form-control" required>
                                        <option value="" disabled {{ old('show_location_fields', $event->show_location_fields) === null ? 'selected' : '' }}>Seleccione si desea mostrar País, Región y Ciudad.</option>
                                        <option value="0" {{ old('show_location_fields', $event->show_location_fields) == 0 ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ old('show_location_fields', $event->show_location_fields) == 1 ? 'selected' : '' }}>Si</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="m-t-20">¿Permitir pago por transferencia? *</label>
                                    @php $allowBankTransfer = old('allow_bank_transfer', $event->allow_bank_transfer); @endphp
                                    <select name="allow_bank_transfer" class="form-control" required>
                                        <option value="" disabled {{ $allowBankTransfer === null ? 'selected' : '' }}>Seleccione si permite pago por transferencia.</option>
                                        <option value="1" {{ $allowBankTransfer ? 'selected' : '' }}>Si</option>
                                        <option value="0" {{ !$allowBankTransfer ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>


                                <div class="col-md-12">
                                    <label class="m-t-20">Imagen General</label><br>
                                    <input type="file" name="photo" class="form-control" required>
                                    <small class="d-block mb-1 text-muted">Imagen principal del evento</small>
                                    <div class="image-block">
                                        <img src="{{ $event->photo }}" class="img-responsive media-preview" alt="Imagen general">
                                    </div>
                                </div>

                                <div class="col-md-12 mt-4 text-center">
                                    <button class="btn btn-primary">Guardar Evento</button>
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
        }
        .removable {
            position: relative;
        }
        .remove-toggle {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .remove-badge {
            position: absolute;
            top: 0px;
            right: 0px;
            width: 18px;
            height: 18px;
            line-height: 18px;
            text-align: center;
            border-radius: 50%;
            background: rgba(0,0,0,0.65);
            color: #fff;
            font-weight: bold;
            /* font-size: 16px; */
            cursor: pointer;
            z-index: 2;
            margin-top: 0 !important;
        }
        .remove-toggle:checked + .remove-badge {
            display: none;
        }
        .remove-toggle:checked + .remove-badge + img,
        .footer-item .remove-toggle:checked + .remove-badge + .footer-thumb {
            display: none;
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
        .footer-item {
            display: inline-block;
            margin: 6px;
            text-align: center;
            position: relative;
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

        console.log('pre-ticket');
        $('body').on('click', '.add-ticket .btn', function(){
            template = '<div class="row ticket-wrapper">\
                    <div class="col-6">\
                        <small>Entrada</small>\
                        <input type="text" class="form-control" name="tickets[0][name]" placeholder="Nombre entrada" required>\
                    </div>\
                    <div class="col-6">\
                        <small>Entrada Inglés</small>\
                        <input type="text" class="form-control" name="tickets[0][name_eng]" placeholder="Nombre entrada en inglés">\
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
                        <input type="text" class="form-control currency" name="tickets[0][price]" placeholder="Valor entrada" required>\
                    </div>\
                    <div class="col-3">\
                        <small>Stock</small>\
                        <input type="number" class="form-control" name="tickets[0][stock]" placeholder="Stock" required>\
                    </div>\
                    <div class="col-3">\
                        <small>Desde</small>\
                        <input type="date" class="form-control date" name="tickets[0][from]" placeholder="Desde" required>\
                    </div>\
                    <div class="col-3">\
                        <small>Hasta</small>\
                        <input type="date" class="form-control date" name="tickets[0][to]" placeholder="Hasta" required>\
                    </div>\
                      <div class="col-3">\
                          <small>Ticket Obligatorio</small>\
                          <select  class="form-control  ticket-is-mandatory" name="tickets[0][is_mandatory]" placeholder="Seleccione si es un campo obligatorio" required>\
                            <option value="false" selected>No</option>\
                            <option value="true">Si</option>\
                          </select>\
                    </div>\
                                        <div class="col-3">\
                                                    <small>Requiere Documento</small>\
                                                    <select  class="form-control ticket-requires-document" name="tickets[0][requires_document]" placeholder="Seleccione si requiere documento" required>\
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
                                            <input type="text" class="form-control" name="inputs[0][name]" placeholder="Nombre de campo" required>\
                                        </div>\
                                        <div class="col-6">\
                                            <small>Nombre de Campo - Inglés</small>\
                                            <input type="text" class="form-control" name="inputs[0][name_eng]" placeholder="Nombre de campo en inglés" required>\
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
                const value = '{{ $event->max_selection_ticket }}';
                if( $(this).val() === '0')
                    input.attr("disabled","true").val(1);
                else
                    input.removeAttr('disabled').val(value);
            });
            $('#is_multiple_selection_ticket').change();
        });
    </script>
@endsection
