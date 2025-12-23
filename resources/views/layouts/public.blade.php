<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
   <title>Masso Eventos - {{ $title or 'Organización de eventos y servicios turísticos' }}</title>

   <link href="{{ mix('css/public.css') }}" rel="stylesheet" type="text/css" />   
   <style>
      /* Custom common styles */
      .small-btn{
         font-size: 12px;
         height: 30px;
         line-height: 30px;
      }
   </style>
   <link rel="icon" type="image/png" href="/favicon.jpg">
   <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
   <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
   <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
      <![endif]-->

   <!-- Global site tag (gtag.js) - Google Analytics -->
   <script async src="https://www.googletagmanager.com/gtag/js?id=UA-143770263-1"></script>
   <script>
     window.dataLayer = window.dataLayer || [];
     function gtag(){dataLayer.push(arguments);}
     gtag('js', new Date());

     gtag('config', 'UA-143770263-1');
   </script>
</head>

<body class="{{ $bodyClass or '' }}">
   <div class="body-inner">
      <header id="header" class="header header-classic">
         <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light">
               <a class="navbar-brand" href="/">
                 <img src="/images/logo.jpg" alt="">
              </a>
               <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
                  aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon"><i class="icon icon-menu"></i></span>
               </button>

               <div class="collapse navbar-collapse" id="navbarNavDropdown">
                  <ul class="navbar-nav ml-auto">
                     <li class="dropdown nav-item @if( $currentRoute == 'public.index') active @endif">
                        <a href="/">Inicio</a>
                     </li>
                     <li class="dropdown nav-item @if( $currentRoute == 'public.about') active @endif">
                        <a href="/somos" >Quiénes Somos</a>
                     </li>
                     
                     <li class="nav-item dropdown @if( $currentRoute == 'public.previously') active @endif">
                        <a href="/eventos-anteriores">Eventos Anteriores</a>
                     </li>
                     <li class="dropdown nav-item @if( $currentRoute == 'public.payment') active @endif">
                        <a href="/pagos" >Pagos grupales</a>
                     </li>
                     <li class="nav-item @if( $currentRoute == 'public.contact') active @endif">
                        <a href="/contacto">Contacto</a>
                     </li>
                     <!-- <li class="header-ticket nav-item">
                        <a class="ticket-btn btn" href="/certificados"> Certificados
                       </a>
                     </li> -->
                  </ul>
               </div>

            </nav>
         </div>
      </header>

      @yield('content')

      <div class="footer-area">

         <footer class="ts-footer" style="background-image: url(./images/shap/subscribe_pattern.png)">
            <div class="container">
               <div class="row">
                  <div class="col-lg-8 mx-auto">
                     <div class="footer-menu text-center mb-25">
                        <ul>
                           <li><a href="/somos">Quiénes Somos</a></li>
                           <li><a href="/eventos-anteriores">Eventos Anteriores</a></li>
                           <li><a href="/contacto">Contacto</a></li>
                           <!-- <li><a href="/certificados">Certificados</a></li> -->
                        </ul>
                     </div>
                     <div class="copyright-text text-center">
                        <p>Masso Eventos © {{ date('Y') }}. Todos los Derechos Reservados.</p>
                     </div>
                  </div>
               </div>
            </div>
         </footer>

         <div class="BackTo">
            <a href="#" class="fa fa-angle-up" aria-hidden="true"></a>
         </div>
      </div>
      <script src="{{ mix('js/public.js') }}"></script> 

      @yield('footer')
      
   </div>
</body>

</html>