@extends('layouts.panel')

@section('content')
    
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h4 class="text-themecolor">Panel de Control</h4>
        </div>
        <div class="col-md-7 align-self-center text-right">
            <div class="d-flex justify-content-end align-items-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Inicio</a></li>
                    <li class="breadcrumb-item active">Panel de Control</li>
                </ol>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            @include('admin.common.flash')
        </div>
    </div>
    
    <div class="row sales-indicators">
        <div class="col-lg-3 col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Recaudado WebPay<br>Eventos Activos</h5>
                    <div class="d-flex no-block align-items-center m-t-10 m-b-10">
                        <div id="sparklinedash3"></div>
                        <div class="ml-auto">
                            <h2 class="text-info"><i class="fa fa-dollar-sign"></i> <span class="counter">{{ number_format($data['payments-active'], 0, ',', '.') }}</span></h2>
                        </div>
                    </div>
                </div>
                <div id="sparkline8" class="sparkchart"></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Inscritos<br>Eventos Activos</h5>
                    <div class="d-flex no-block align-items-center m-t-10 m-b-10">
                        <div id="sparklinedash4"></div>
                        <div class="ml-auto">
                            <h2 class="text-info"><i class="fa fa-user"></i> <span class="counter">{{ $data['enrolled-active'] }}</span></h2>
                        </div>
                    </div>
                </div>
                <div id="sparkline8" class="sparkchart"></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Eventos:<br>Activos</h5>
                    <div class="d-flex no-block align-items-center m-t-10 m-b-10">
                        <div id="sparklinedash"></div>
                        <div class="ml-auto">
                            <h2 class="text-info"><i class="fa fa-calendar-check"></i> <span class="counter">{{ $data['events-active'] }}</span></h2>
                        </div>
                    </div>
                </div>
                <div id="sparkline8" class="sparkchart"></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Eventos<br>Expirados</h5>
                    <div class="d-flex no-block align-items-center m-t-10 m-b-10">
                        <div id="sparklinedash2"></div>
                        <div class="ml-auto">
                            <h2 class="text-info"><i class="fa fa-calendar-minus"></i> <span class="counter">{{ $data['events-expired'] }}</span></h2>
                        </div>
                    </div>
                </div>
                <div id="sparkline8" class="sparkchart"></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Respuestas<br>Encuesta</h5>
                    <div class="d-flex no-block align-items-center m-t-10 m-b-10">
                        <div id="sparklinedash2"></div>
                        <div class="ml-auto">
                            <h2 class="text-info"><i class="mdi mdi-poll"></i> <span class="counter">{{ $data['events-survey'] }}</span></h2>
                        </div>
                    </div>
                </div>
                <div id="sparkline8" class="sparkchart"></div>
            </div>
        </div>

        
    </div>

@endsection
