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
                            <h4 class="card-title">Editar cupones del evento</h4>
                        </div>
                        <div class="col-12">
                            <h6 class="card-subtitle">Aquí puede administrar los diferentes cupones de descuento asociados al evento.</h6>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('events.update-coupons', ['id' => $event->id]) }}" enctype="multipart/form-data" class="row">
                        @csrf
                        <div class="col-12 left-wrapper loading-wrapper">
                            <div class="row">


                                <div class="col-md-12">
                                    <label class="m-t-20 col-12">Cupones</label>
                                    <small class="col-12" style="padding-bottom: 15px;display: block;text-align: justify;">
                                        * Puede ingresar los tipos de cupones disponibles. Puede especificar una fecha de inicio y término, así como un límite de usos disponible para el cupón. Si deja vacío estos campos, se entiende que no existirán restricciones.
                                    </small>

                                    @php
                                        $coupons = !empty(old('coupons')) ? old('coupons') : ($event->coupons ?? []);
                                    @endphp

                                    @foreach($coupons as $key => $coupon)
                                        @php
                                            // Para modelos, accedemos directo al atributo
                                            $isModel = is_object($coupon);
                                            $key = $isModel ? $coupon->id : $key;

                                            $coupon['coupon_tickets'] = $isModel? $coupon->tickets->pluck('id')->toArray() : $coupon['coupon_tickets'];
                                        @endphp

                                        <div class="row ticket-wrapper" data-num="0">
                                            <div class="col-12">
                                                <small>Código *</small>
                                                <input type="text" class="form-control" name="coupons[{{ $key }}][code]" value="{{ $isModel ? $coupon->code : $coupon['code'] }}" placeholder="Código del cupón" required>
                                            </div>
                                            <div class="col-6">
                                                <small>Porcentaje de descuento *</small>
                                                <input type="number" class="form-control" name="coupons[{{ $key }}][discount_percentage]" value="{{ $isModel ? $coupon->discount_percentage : $coupon['discount_percentage'] }}" placeholder="Ingrese porcentaje (%)" min="1" max="100" required>
                                            </div>
                                            <div class="col-6">
                                                <small>Límite de usos</small>
                                                <input type="number" class="form-control" name="coupons[{{ $key }}][usage_limit]" value="{{ $isModel ? $coupon->usage_limit : $coupon['usage_limit'] }}" placeholder="Límite de usos" min="1">
                                            </div>
                                            @php
                                                $startsAt = $isModel ? $coupon->starts_at : $coupon['starts_at'];
                                                $endsAt = $isModel ? $coupon->ends_at : $coupon['ends_at'];
                                            @endphp

                                            <div class="col-6">
                                                <small>Desde</small>
                                                <input type="date" class="form-control date" name="coupons[{{ $key }}][starts_at]" value="{{ $startsAt ?: '' }}" placeholder="Desde">
                                            </div>
                                            <div class="col-6">
                                                <small>Hasta</small>
                                                <input type="date" class="form-control date" name="coupons[{{ $key }}][ends_at]" value="{{ $endsAt ?: '' }}" placeholder="Hasta">
                                            </div>
                                            <div class="col-12">
                                                <small>Tickets a los que aplica el cupón</small>
                                                
                                                <select class="select2 select2-multiple form-control"
                                                        style="width: 100%"
                                                        multiple="multiple"
                                                        name="coupons[{{ $key }}][coupon_tickets][]"
                                                        data-placeholder="Seleccione tickets">
                                                    
                                                    @foreach ($event->tickets as $ticket)
                                                        <option value="{{ $ticket->id }}"
                                                            @if (!empty($coupon['coupon_tickets']) && in_array($ticket->id, $coupon['coupon_tickets']))
                                                                selected
                                                            @endif
                                                        >{{ $ticket->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                                                                        
                                            <small class="col-12 text-right trash">
                                                <span class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Eliminar</span>
                                            </small>
                                        </div>
                                    @endforeach

                                    <div class="col-12 mt-3 add-coupon text-right">
                                        <span class="btn btn-dark">Añadir cupón</span>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <button class="btn btn-primary">Guardar cupones</button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

<style>
.select2-container .select2-search--inline {

            margin-left: 8px !important;
        }
</style>
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
        function initDatePickers() {
            $('.date:not(.picker-initialized)').each(function () {
                $(this).bootstrapMaterialDatePicker({
                weekStart: 1,
                time: false,
                clearButton: true,
                clearText: 'Limpiar',
                cancelText: 'Cancelar'
                }).addClass('picker-initialized');
            });
        }
    
       initDatePickers();

       $('body').on('click', '.ticket-wrapper .trash span', function(){

            if( confirm('¿Esta seguro?') ){
                ele = $(this).parent().parent().remove();
            }

       });

        $('body').on('click', '.add-coupon .btn', function () {
            const i = Date.now(); // Para usar como índice único

            const template = `
            <div class="row ticket-wrapper" data-num="${i}">
                <div class="col-12">
                    <small>Código *</small>
                    <input type="text" class="form-control" name="coupons[${i}][code]" placeholder="Código del cupón" required>
                </div>
                <div class="col-6">
                    <small>Porcentaje de descuento *</small>
                    <input type="number" class="form-control" name="coupons[${i}][discount_percentage]" placeholder="Ingrese porcentaje (%)" min="1" max="100" required>
                </div>
                <div class="col-6">
                    <small>Límite de usos</small>
                    <input type="number" class="form-control" name="coupons[${i}][usage_limit]" placeholder="Límite de usos" min="1">
                </div>
                <div class="col-6">
                    <small>Desde</small>
                    <input type="date" class="form-control date" name="coupons[${i}][starts_at]" placeholder="Desde">
                </div>
                <div class="col-6">
                    <small>Hasta</small>
                    <input type="date" class="form-control date" name="coupons[${i}][ends_at]" placeholder="Hasta">
                </div>
                <div class="col-12">
                    <small>Tickets a los que aplica el cupón</small>
                    <select class="select2 select2-multiple form-control" style="width: 100%" multiple="multiple" name="coupons[${i}][coupon_tickets][]" data-placeholder="Seleccione tickets">
                        @foreach ($event->tickets as $ticket)
                            <option value="{{ $ticket->id }}">{{ $ticket->name }}</option>
                        @endforeach
                    </select>
                </div>
                <small class="col-12 text-right trash">
                    <span class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Eliminar</span>
                </small>
            </div>`;

            $('.add-coupon').before(template);

            // Inicializar select2 para el nuevo elemento
            $(`select[name="coupons[${i}][coupon_tickets][]"]`).select2({
                placeholder: "Seleccione tickets"
            });

            initDatePickers();    

        });

        $(function () {
            $(".select2").each(function(i,v){
                var that = $(this);
                var placeholder = $(that).attr("data-placeholder");
                $(that).select2({
                    placeholder:placeholder
                });
            });
        });
    </script>
@endsection
