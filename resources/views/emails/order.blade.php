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
<h1 style="text-align: center; color: white">
COMPROBANTE DE PAGO / TICKET OF PAYMENT
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
      <p>Estimado cliente, usted ha efectuado un pago con ID <b>#{{ $payment->id }}</b> por un monto total de CLP ${{ number_format($payment->amount, 0,',','.') }}, mediante la glosa <b>'{{ $payment->description }}'</b>, el cual ha sido recepcionado de forma exitosa. A continuación se detalla la información asociada a la transacción.<br><br>

        Dear customer, you have made a payment with ID <b>#{{ $payment->id }}</b> for a total amount of CLP${{ number_format($payment->amount, 0,',','.') }}, using the <b>'{{ $payment->description }}'</b> gloss, which has been successfully received. The information associated with the transaction is detailed below.<br></p>
      </div>

      <div class="col s12 m10 offset-m1">
        <p>Detalles del Participante / Participant Details<br></p>
          <table border="0" cellpadding="0" cellspacing="0" style="width: 100%; text-align: left;">
            <tbody>
              <tr>
                <td style="padding: 2px 5px; background-color: #f0f0f0; color: black; text-align: left;" >Nombres / Names</td>
                <td style="padding: 2px 5px; text-align: left;">{{ $payment->name }}</td>
              </tr>
              <tr>
                <td style="padding: 2px 5px; background-color: #f0f0f0; color: black; text-align: left;">Apellidos / Lastname</td>
                <td style="padding: 2px 5px; text-align: left;">{{ $payment->lastname }} </td>
              </tr>
              <tr>
                <td style="padding: 2px 5px; background-color: #f0f0f0; color: black; text-align: left;">Correo / Email</td>
                <td style="padding: 2px 5px; text-align: left;">{{ $payment->email }}</td>
              </tr>
              <tr>
                <td style="padding: 2px 5px; background-color: #f0f0f0; color: black; text-align: left;">Pago / Payment Description</td>
                <td style="padding: 2px 5px; text-align: left;">
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
                <td style="padding: 2px 5px; background-color: #f0f0f0; color: black; text-align: left;">Monto / Amount</td>
                <td style="padding: 2px 5px; text-align: left;">CLP ${{ number_format($payment->amount, 0,',','.') }}</td>
              </tr>

            </tbody>
          </table>
      </div>

      @if($payment->success && $payment->success->get()->isNotEmpty())
      <div class="col s12 m10 offset-m1">
      <p>Detalles del Pago / Payment Details<br></p>
          <table border="0" cellpadding="0" cellspacing="0" style="width: 100%; text-align: left;">
            <tbody>
              <tr>
                <td style="padding: 2px 5px; background-color: #f0f0f0; color: black; text-align: left;" >Cod. Transacción / Transaction Code</td>
                <td style="padding: 2px 5px; text-align: left;">{{ $payment->success->id }}</td>
              </tr>
              <tr>
                <td style="padding: 2px 5px; background-color: #f0f0f0; color: black; text-align: left;">Cód. Autorización / Auth Code</td>
                <td style="padding: 2px 5px; text-align: left;">{{ $payment->success->auth_code }}</td>
              </tr>
              <tr>
                <td style="padding: 2px 5px; background-color: #f0f0f0; color: black; text-align: left;">Monto Pagado / Amount Payment</td>
                <td style="padding: 2px 5px; text-align: left;">${{ number_format($payment->amount, 0,',','.') }}</td>
              </tr>
              <tr>
                <td style="padding: 2px 5px; background-color: #f0f0f0; color: black; text-align: left;">Tipo de Pago / Payment Type</td>
                <td style="padding: 2px 5px; text-align: left;">{{ $payment->success->typePayment() }}</td>
              </tr>
              <tr>
                <td style="padding: 2px 5px; background-color: #f0f0f0; color: black; text-align: left;">Cuotas / Fees</td>
                <td style="padding: 2px 5px; text-align: left;">{{ $payment->success->quotes }}</td>
              </tr>
              <tr>
                <td style="padding: 2px 5px; background-color: #f0f0f0; color: black; text-align: left;">Tarjeta / Card Numbers</td>
                <td style="padding: 2px 5px; text-align: left;">XXXX XXXX XXXX {{ $payment->success->card_number }}</td>
              </tr>
            </tbody>
          </table>
      </div>
      @endif
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
