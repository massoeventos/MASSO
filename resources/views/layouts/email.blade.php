<html>
	<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
		<div id="wrapper" dir="ltr" style="background-color:#f7f7f7;margin:0;padding:30px 0 70px 0;width:100%">
			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
				<tr>
					<td align="center" valign="top">
						<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">
							<tr>
								<td align="center" valign="top">
									<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header">
										<tr>
											<td>
												<center>
													<img src="{{ url('/images/logo.jpg') }}" width='150px' alt="SkiTour" />
												</center>
											</td>
										</tr>
										<tr>
											<td id="header_wrapper" style="background-color: #0076BA">
												<h1 style="text-align: center; color: white">
													{{ $subject }}
												</h1>
											</td>
										</tr>
									</table>
									<!-- End Header -->
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<!-- Body -->
									<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body" style="background-color: white;">
										<tr>
											<td valign="top" id="body_content">
												<!-- Content -->
												<table border="0" cellpadding="20" cellspacing="0" width="100%">
													<tr>
														<td valign="top">
															<div id="body_content_inner">
																@yield('content')
															</div>
														</td>
													</tr>
													<tr>
														<td>
															<center>
																<p>En caso de cualquier duda o consulta, favor contactar al correo contacto@skitour.cl</p>
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
							            
		                  

