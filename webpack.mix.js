const { mix } = require('laravel-mix');

mix.styles([
    'resources/assets/template/css/bootstrap.min.css',
    'resources/assets/template/css/font-awesome.min.css',
    'resources/assets/template/css/animate.css',
    'resources/assets/template/css/magnific-popup.css',
    'resources/assets/template/css/owl.carousel.min.css',
    'resources/assets/template/css/isotop.css',
    'resources/assets/template/css/xsIcon.css',
    'resources/assets/template/css/style.css',
    'resources/assets/template/css/responsive.css',

    ], 'public/css/public.css').version();

mix.styles([
    'resources/assets/material/dist/css/pages/login-register-lock.css',
    'resources/assets/material/dist/css/style.css'
    ], 'public/css/frontend.css').version();

mix.styles([
    'resources/assets/node_modules/morrisjs/morris.css',
    'resources/assets/node_modules/toast-master/css/jquery.toast.css',

    'resources/assets/icons/font-awesome/css/fontawesome-all.css',
    'resources/assets/icons/simple-line-icons/css/simple-line-icons.css',
    'resources/assets/icons/weather-icons/css/weather-icons.css',
    'resources/assets/icons/themify-icons/themify-icons.css',
    'resources/assets/icons/flag-icon-css/flag-icon.min.css',
    'resources/assets/icons/material-design-iconic-font/css/materialdesignicons.min.css',


    'resources/assets/node_modules/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css',
    'resources/assets/node_modules/clockpicker/dist/jquery-clockpicker.min.css',
    'resources/assets/node_modules/jquery-asColorPicker-master/dist/css/asColorPicker.css',
    'resources/assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css',
    'resources/assets/node_modules/timepicker/bootstrap-timepicker.min.css',
    'resources/assets/node_modules/bootstrap-daterangepicker/daterangepicker.css',
    'resources/assets/node_modules/select2/dist/css/select2.min.css',
    'resources/assets/node_modules/multiselect/css/multi-select.css',

    'resources/assets/node_modules/html5-editor/bootstrap-wysihtml5.css',
    'resources/assets/material/dist/css/pages/easy-pie-chart.css',

    'resources/assets/material/dist/css/style.css',
    'resources/assets/material/dist/css/pages/dashboard2.css',
    ], 'public/css/panel.css').version();

mix.scripts([
    'resources/assets/template/js/jquery.js',
    'resources/assets/template/js/popper.min.js',
    'resources/assets/template/js/bootstrap.min.js',
    'resources/assets/template/js/jquery.appear.min.js',
    'resources/assets/template/js/jquery.jCounter.js',
    'resources/assets/template/js/jquery.magnific-popup.min.js',
    'resources/assets/template/js/owl.carousel.min.js',
    'resources/assets/template/js/wow.min.js',
    'resources/assets/template/js/isotope.pkgd.min.js',
    'resources/assets/template/js/main.js',
    ], 'public/js/public.js').version();

mix.scripts([
    'resources/assets/node_modules/jquery/jquery-3.2.1.min.js',
    'resources/assets/node_modules/popper/popper.min.js',
    'resources/assets/node_modules/bootstrap/dist/js/bootstrap.min.js',
    'resources/assets/vendors/jquery-rut/jquery.rut.js',
    'resources/assets/material/dist/js/mkr-dash.js',
    ], 'public/js/frontend.js').version();


mix.scripts([
    'resources/assets/node_modules/jquery/jquery-3.2.1.min.js',
    'resources/assets/node_modules/popper/popper.min.js',
    'resources/assets/node_modules/bootstrap/dist/js/bootstrap.min.js',
    'resources/assets/material/dist/js/perfect-scrollbar.jquery.min.js',
    'resources/assets/material/dist/js/waves.js',
    'resources/assets/material/dist/js/sidebarmenu.js',
    'resources/assets/material/dist/js/custom.js',
    'resources/assets/node_modules/raphael/raphael-min.js',
    'resources/assets/node_modules/morrisjs/morris.min.js',
    'resources/assets/node_modules/jquery-sparkline/jquery.sparkline.min.js',
    'resources/assets/node_modules/toast-master/js/jquery.toast.js',


    'resources/assets/node_modules/moment/moment.js',
    'resources/assets/node_modules/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js',
    'resources/assets/node_modules/clockpicker/dist/jquery-clockpicker.min.js',
    'resources/assets/node_modules/jquery-asColor/dist/jquery-asColor.js',

    'resources/assets/node_modules/select2/dist/js/select2.full.min.js',
    'resources/assets/node_modules/multiselect/js/jquery.multi-select.js',
    'resources/assets/vendors/howler/howler.js',

    'resources/assets/node_modules/jquery-asGradient/dist/jquery-asGradient.js',
    'resources/assets/node_modules/jquery-asColorPicker-master/dist/jquery-asColorPicker.min.js',
    'resources/assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js',
    'resources/assets/node_modules/timepicker/bootstrap-timepicker.min.js',
    'resources/assets/node_modules/bootstrap-daterangepicker/daterangepicker.js',

    'resources/assets/node_modules/html5-editor/wysihtml5-0.3.0.js',
    'resources/assets/node_modules/html5-editor/bootstrap-wysihtml5.js',

    'resources/assets/material/dist/js/dashboard1.js',
    ], 'public/js/panel.js').version();


mix.copy('resources/assets/images', 'public/images', false);
mix.copy('resources/assets/material/dist/css/fonts', 'public/fonts', false);
mix.copy('resources/assets/material/dist/css/fonts', 'public/css/fonts', false);
mix.copy('resources/assets/icons/weather-icons/fonts', 'public/fonts', false);
mix.copy('resources/assets/icons/weather-icons/fonts', 'public/fonts', false);