<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Area TI">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon.jpg">
    <title>{{ $title or 'Panel de Administración' }} | Massó Eventos </title>
    <style type="text/css">
        @import url(https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700);
    </style>
    <link href="{{ mix('css/panel.css') }}" rel="stylesheet" type="text/css" />

    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style>
        .allow-edit {
            position: relative;
        }
        .allow-edit::after {
            position: absolute;
            content: "Editar";
            right: 5px;
            cursor: pointer;
            text-transform: lowercase;
        }
    </style>

</head>

<body class="skin-default fixed-layout {{ $bodyClass or '' }} mini-sidebar force-mini-on-init" >

    <div class="preloader">
        <div class="loader">
            <div class="loader__figure"></div>
            <p class="loader__label">Massó Eventos</p>
        </div>
    </div>



    <div id="main-wrapper">

        <header class="topbar gradient">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">

                <div class="navbar-header">
                    <a class="navbar-brand" href="{{ route('dashboard.index') }}">
                        <b>
                            <img src="/favicon.jpg" height="30px" alt="homepage" class="dark-logo" />
                            <img src="/favicon.jpg" height="30px" alt="homepage" class="light-logo" />
                        </b>
                    </a>
                </div>

                <div class="navbar-collapse">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item"> <a class="nav-link nav-toggler d-block d-md-none waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </li>
                        <li class="nav-item"> <a class="nav-link sidebartoggler d-none d-lg-block d-md-block waves-effect waves-dark" href="javascript:void(0)"><i class="icon-menu"></i></a> </li>

                    </ul>

                    <ul class="navbar-nav my-lg-0">

                        @if( $authUser->role_id == 6 && !empty($pickingData) )

                            <li class="nav-item dropdown show waiting-nv-notification" >
                                <a class="nav-link waves-effect waves-dark" href="{{ route('b.picking.process.pending') }}" id="2">
                                    <small><b>{{ $pickingData['waiting'] }}</b> En Espera</small>
                                    <i class="fa fa-clock"></i>
                                    @if( $pickingData['waiting'] > 0 )
                                    <div class="notify"> <span class="heartbit"></span> <span class="point"></span></div>
                                    @endif
                                </a>
                            </li>

                            <li class="nav-item dropdown show pending-nv-notification" data-nv={{ $pickingData['latest'] }}>
                                <a class="nav-link waves-effect waves-dark" href="{{ route('b.picking.process.index') }}" id="2">
                                    <small><b>{{ $pickingData['pending'] }}</b> Pendientes</small>
                                    <i class="fa fa-bell"></i>
                                    @if( $pickingData['pending'] > 0 )
                                    <div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
                                    @endif
                                </a>
                            </li>

                        @endif

                        @if( $authUser->role_id == 7 && !empty($pickingData) )

                            <li class="nav-item dropdown show supervise-nv-notification" data-nv={{ $pickingData['latestsupervise'] }}>
                                <a class="nav-link waves-effect waves-dark" href="{{ route('b.picking.supervise.index') }}" id="2">
                                    <small><b>{{ $pickingData['supervise'] }}</b> Fiscalizar</small>
                                    <i class="fa fa-clock"></i>
                                    @if( $pickingData['supervise'] > 0 )
                                    <div class="notify"> <span class="heartbit"></span> <span class="point"></span></div>
                                    @endif
                                </a>
                            </li>

                            <li class="nav-item dropdown show waiting-nv-notification">
                                <a class="nav-link waves-effect waves-dark" href="{{ route('b.picking.process.pending') }}" id="2">
                                    <small><b>{{ $pickingData['waiting'] }}</b> En Espera</small>
                                    <i class="fa fa-clock"></i>
                                    @if( $pickingData['waiting'] > 0 )
                                    <div class="notify"> <span class="heartbit"></span> <span class="point"></span></div>
                                    @endif
                                </a>
                            </li>

                            <li class="nav-item dropdown show pending-nv-notification" data-nv={{ $pickingData['latest'] }}>
                                <a class="nav-link waves-effect waves-dark" href="{{ route('b.picking.process.index') }}" id="2">
                                    <small><b>{{ $pickingData['pending'] }}</b> Pendientes</small>
                                    <i class="fa fa-bell"></i>
                                    @if( $pickingData['pending'] > 0 )
                                    <div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
                                    @endif
                                </a>
                            </li>

                        @endif

                        <li class="nav-item dropdown u-pro">
                            <a class="nav-link dropdown-toggle waves-effect waves-dark profile-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="hidden-md-down">{{ $authUser->getName() }} &nbsp;<i class="fa fa-angle-down"></i></span> </a>
                            <div class="dropdown-menu dropdown-menu-right animated flipInY">
                                <a href="{{ route('logout.index') }}" class="dropdown-item"><i class="fa fa-power-off"></i> Cerrar Sesión</a>
                            </div>
                        </li>

                    </ul>


                </div>
            </nav>
        </header>


        @include('admin.common.menu')

        <div class="page-wrapper">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>

        <footer class="footer">
            Plataforma de Gestión / v1 / Massó Eventos © {{ date('Y') }}
        </footer>
    </div>

    <style type="text/css">

.right-wrapper img {
    max-width: 100%;
    max-height: 200px;
    margin: 0 auto;
    display: block;
}
    </style>
    <script src="{{ mix('js/panel.js') }}"></script>
    <script>
        $('#chat, #msg, #comment, #todo').perfectScrollbar();
    </script>
    @yield('footer');

    @if( $authUser->role_id == 6 || $authUser->role_id == 7 )
        @include('admin.bodega.common.check_assign')
    @endif

</body>

</html>
