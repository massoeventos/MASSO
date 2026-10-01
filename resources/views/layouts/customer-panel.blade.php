<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Area TI">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon.jpg">
    <title>{{ $title ?? 'Mi Cuenta' }} | Massó Eventos </title>
    <style type="text/css">
        @import url(https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700);
    </style>
    <link href="{{ mix('css/panel.css') }}" rel="stylesheet" type="text/css" />

    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body class="skin-default fixed-layout {{ $bodyClass ?? '' }} mini-sidebar force-mini-on-init" >

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
                    <a class="navbar-brand" href="{{ route('customer.account') }}">
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
                        <li class="nav-item dropdown u-pro">
                            <a class="nav-link dropdown-toggle waves-effect waves-dark profile-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="hidden-md-down">
                                    {{ Auth::guard('customer')->user()->name ?: Auth::guard('customer')->user()->email }} &nbsp;<i class="fa fa-angle-down"></i>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right animated flipInY">
                                <a href="{{ route('customer.profile') }}" class="dropdown-item"><i class="fa fa-user"></i> Mi Perfil</a>
                                <form method="POST" action="{{ route('customer.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="fa fa-power-off"></i> Cerrar Sesión</button>
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        @include('customer.common.menu')

        <div class="page-wrapper">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>

        <footer class="footer">
            Mi Cuenta / Massó Eventos © {{ date('Y') }}
        </footer>
    </div>

    <script src="{{ mix('js/panel.js') }}"></script>
    @yield('footer')

</body>

</html>
