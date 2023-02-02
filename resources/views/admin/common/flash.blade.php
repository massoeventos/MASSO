@if(Session::has('error_alert'))
	<div class="alert alert-danger flash-alert">{!! Session::get('error_alert') !!}</div>
@endif

@if(Session::has('success_alert'))
	<div class="alert alert-success" style="color: black;">{!! Session::get('success_alert') !!}</div>
@endif