@extends('layouts.email')

@section('email_content')

	<tr>
		<td>
			<p><center>Estimados, se ha recibido un nuevo mensaje a través del formulario de contacto de Extremezone.cl.<br>Los detalles se indican a continuación.</center></p>
		</td>
	</tr>
	<tr>
		<td valign="top" align="left">
			<table border="0" cellpadding="3" cellspacing="1" width="100%" style="width:100%;font-family:Verdana,sans-serif;font-size:11px">
				<thead>
					<tr>
						<th align="left" style="background-color:#e5e5e5">Detalles del Mensaje.</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td align="left" style="background-color:#f5f5f5">
							Nombre: <strong>{{ $data['name'] }}</strong>
							<br>
							Correo: <strong>{{ $data['email'] }}</strong>
							<br>
							Teléfono: <strong>{{ $data['phone'] }}</strong>
							<br>
							Mensaje:<br><br>
							{{ $data['comment'] }}
							<br>
							<br>
						</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>						
										

@endsection