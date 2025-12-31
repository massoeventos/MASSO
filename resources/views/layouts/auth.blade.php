<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Plataforma de Pagos MassoEventos">
    <meta name="author" content="obravoo.com">

    <link rel="icon" type="image/png" sizes="16x16" href="/favicon.jpg">
    <title>{{ $title ?? 'Sistema de Administración'}} | Massó Eventos</title>
    <link href="{{ mix('css/frontend.css') }}" rel="stylesheet" type="text/css" />
    
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body class="skin-default card-no-border">

    <div class="preloader">
        <div class="loader">
            <div class="loader__figure"></div>
            <p class="loader__label">Massó Eventos</p>
        </div>
    </div>


    @yield('content')
    
    <script src="{{ mix('js/frontend.js') }}"></script> 

    <script type="text/javascript">
        $(function() {
            $(".preloader").fadeOut();
        });
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>
    
</body>

</html>
