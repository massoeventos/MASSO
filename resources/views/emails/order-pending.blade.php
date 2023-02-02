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
COMPROBANTE DE REGISTRO<br><small>TICKET OF REGISTRATION</small>
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
      <p>Estimado cliente, se ha procesado su registro <b>#{{ $payment->id }}</b> por un monto total de CLP ${{ number_format($payment->amount, 0,',','.') }}, mediante la glosa <b>'{{ $payment->description }}'</b>. A continuación se muestra el detalle del registro para que pueda procesar el pago mediante transferencia a una de las siguientes cuentas bancarias.<br><br>

        <small>Dear customer, you have made a registration with ID <b>#{{ $payment->id }}</b> for a total amount of CLP${{ number_format($payment->amount, 0,',','.') }}, using the <b>'{{ $payment->description }}'</b> gloss. Below is the registration detail so you can process the payment by transfer to one of the following bank accounts.</small><br><br></p>
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
		<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; text-align: left;">
			<thead>
				<tr style="background-color: #801380; color: white; font-size: 16px; margin: 0;">
					<th colspan="2" style="background-color: #801380; padding: 5px 10px; color: white; font-size: 16px; margin: 0;">Cuentas Bancarias para Transferencias<br><small>Bank Accounts for Transfers</small></th>
				</tr>
			</thead>
			<tbody>
			<tr>
				<td style="padding: 2px 5px; background-color: #f0f0f0; color: black; text-align: left;">
					<br>
					<b>International Transfers</b><br>
					<b>Currency:</b> Chilean Pesos <br>
					<b>Swift Code:</b> BCHICLRM<br>
					<b>Bank:</b> Banco de Chile<br>
					<b>Bank Account:</b> 1503095-04<br>
					<b>Beneficiary:</b> Paola Massó Masseventos E.I.R.L.<br>
					<b>Bank Address:</b> Ahumada 251, Santiago, Chile<br>
					<b>E-mail:</b> contacto@massoeventos.cl<br>
					<br>
				</td>
				<td style="padding: 2px 5px; color: black; text-align: left;">

					<b>Transferencias Nacionales</b><br>
					<b>Cuenta Cte. Nº:</b> 00-015-03095-04<br>
					<b>Banco:</b> Chile<br>
					<b>Titular:</b> Paola Massó masseventos E.I.R.L.<br>
					<b>Rut:</b> 52.001.885-9<br>
					<b>E-mail:</b> contacto@massoeventos.cl<br>
				</td>
			</tr>




			</tbody>
		</table>
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
