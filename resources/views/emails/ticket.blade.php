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
BOLETA ELECTRÓNICA
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
      <p>Estimado cliente,<br>adjuntamos la boleta electrónica asociada a su pago ID <b>#{{ $payment->id }}</b> por un monto total de ${{ number_format($payment->amount, 0,',','.') }}, mediante la glosa <b>'{{ $payment->description }}'</b>. <br><br>La boleta se encuentra adjunta a este correo.<br>Saludos cordiales</p>
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