<html>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
<div id="wrapper" dir="ltr" style="background-color:#f7f7f7;margin:0;padding:70px 0 70px 0;width:100%">
<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
<tr>
<td align="center" valign="top">
<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">
<tr>
<td align="center" valign="top">
<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header">
<tr>
<td id="header_wrapper" style="background-color: #801380">
<h1 style="text-align: center; color: white; font-size: 20px; line-height: 20px;">
SOLICITUD DE INSCRIPCIÓN - PENDIENTE DE PAGO<br><small>REGISTRATION REQUEST - PAYMENT PENDING</small>
</h1>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td align="center" valign="top">
<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body" style="background-color: white;">
<tr>
<td valign="top" id="body_content">
<table border="0" cellpadding="20" cellspacing="0" width="100%">
<tr>
<td valign="top">
<div id="body_content_inner">
    <div class="row">
      <div class="col s12 m10 offset-m1">
      <p>Estimado cliente, hemos recibido su solicitud de inscripción N° <b>#{{ $payment->id }}</b> por un monto total de CLP ${{ number_format($payment->amount, 0,',','.') }}, mediante la glosa <b>'{{ $payment->description }}'</b>. <b>Su inscripción quedará confirmada una vez que recibamos el pago</b>; en ese momento le enviaremos el comprobante de inscripción.<br><br>

        <small>Dear customer, we have received your registration request N° <b>#{{ $payment->id }}</b> for a total amount of CLP${{ number_format($payment->amount, 0,',','.') }}, using the <b>'{{ $payment->description }}'</b> gloss. <b>Your registration will be confirmed once we receive the payment</b>; at that time we will send you the registration receipt.</small><br><br></p>
      </div>

	<div class="col s12 m10 offset-m1">
		<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; text-align: left;">
			<thead>
				<tr style="background-color: #801380; color: white; font-size: 16px; margin: 0;">
					<th colspan="2" style="background-color: #801380; padding: 5px 10px; color: white; font-size: 16px; margin: 0;">Detalles del Participante / Participant Details</th>
				</tr>
			</thead>
		<tbody>
		  <tr>
		    <td style="padding: 5px 5px 5px; background-color: #f0f0f0; color: black; text-align: left;" ><b>Nombres</b><br><small>Names</small></td>
		    <td style="padding: 5px 5px 5px; background-color: #f0f0f0; color: black; text-align: left;">{{ $payment->name }}</td>
		  </tr>
		  <tr>
		    <td style="padding: 5px 5px 5px; background-color: white; color: black; text-align: left;"><b>Apellidos</b><br><small>Lastname</small></td>
		    <td style="padding: 5px 5px 5px; background-color: white; color: black; text-align: left;">{{ $payment->lastname }} </td>
		  </tr>
		  <tr>
		    <td style="padding: 5px 5px 5px; background-color: #f0f0f0; color: black; text-align: left;"><b>Correo</b><br><small>Email</small></td>
		    <td style="padding: 5px 5px 5px; background-color: #f0f0f0; color: black; text-align: left;">{{ $payment->email }}</td>
		  </tr>
		  <tr>
		    <td style="padding: 5px 5px 5px; background-color: white; color: black; text-align: left;"><b>Pago</b><br><small>Payment Description</small></td>
		    <td style="padding: 5px 5px 5px; background-color: white; color: black; text-align: left;">
                {{ $payment->description }}
                <?php
                $events = $payment->getEvent();
                if(is_array($events) && count($events) > 0) {
                    echo '<ul>';
                    foreach ($events as $event){
                        echo "<li>{$event}</li>";
                    }
                    echo '</ul>';
                } else {
                    echo '-';
                }
                ?>
            </td>
		  </tr>
		  <tr>
		    <td style="padding: 5px 5px; background-color: #f0f0f0; color: black; text-align: left;"><b>Monto</b><br><small>Amount</small></td>
		    <td style="padding: 5px 5px 5px; background-color: #f0f0f0; color: black; text-align: left;">CLP ${{ number_format($payment->amount, 0,',','.') }}</td>
		  </tr>

		</tbody>
		</table><br><br>
	</div>




	<div class="col s12 m10 offset-m1">
		@if( in_array($payment->managment, ['transfer', 'transfer2']) )
		<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; text-align: left;">
			<thead>
				<tr style="background-color: #801380; color: white; font-size: 16px; margin: 0;">
					<th style="background-color: #801380; padding: 5px 10px; color: white; font-size: 16px; margin: 0;">Referencia para el pago / Payment reference</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="padding: 10px; background-color: #f0f0f0; color: black; text-align: left;">
						N° de orden / Order N°: <b style="font-size: 18px;">#{{ $payment->id }}</b> &nbsp;·&nbsp; {{ $payment->name }} {{ $payment->lastname }}<br><br>
						Al realizar la transferencia, incluso si la hace un tercero o una institución, indique el N° de orden <b>#{{ $payment->id }}</b> en el comentario y envíe el comprobante a <a href="mailto:{{ \Masso\Payment::PAYMENT_CONTACT_EMAIL }}">{{ \Masso\Payment::PAYMENT_CONTACT_EMAIL }}</a>.<br><br>
						<small>When making the transfer, even if it is made by a third party or an institution, include the order N° <b>#{{ $payment->id }}</b> in the comment and send the receipt to <a href="mailto:{{ \Masso\Payment::PAYMENT_CONTACT_EMAIL }}">{{ \Masso\Payment::PAYMENT_CONTACT_EMAIL }}</a>.</small>
					</td>
				</tr>
			</tbody>
		</table><br>
		@elseif( $payment->event )
		<p style="padding: 10px; background-color: #f0f0f0; color: black;">
			Puede completar su pago volviendo al formulario de inscripción del evento: <a href="{{ route('public.event', $payment->event->slug) }}">{{ $payment->event->name }}</a>.<br><br>
			<small>You can complete your payment by returning to the event registration form.</small>
		</p>
		@endif

		<p style="padding: 10px; background-color: #f0f0f0; color: black;">
			Si tiene problemas para realizar su pago, comuníquese con nosotros a <a href="mailto:{{ \Masso\Payment::PAYMENT_CONTACT_EMAIL }}">{{ \Masso\Payment::PAYMENT_CONTACT_EMAIL }}</a>.<br><br>
			<small>If you have trouble making your payment, please contact us at <a href="mailto:{{ \Masso\Payment::PAYMENT_CONTACT_EMAIL }}">{{ \Masso\Payment::PAYMENT_CONTACT_EMAIL }}</a>.</small>
		</p>
	</div>


    </div>

</div>
</td>
</tr>
<tr>
<td>
<center>
<img src="https://www.massoeventos.cl/images/logo.jpg" height="100" alt="Massó Eventos" />
</center>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
</div>
</body>
</html>
