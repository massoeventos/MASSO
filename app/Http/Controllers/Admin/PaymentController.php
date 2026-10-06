<?php
namespace Masso\Http\Controllers\Admin;
use Masso\Http\Requests\PaymentStoreRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Masso\Http\Requests\PaymentUpdateRequest;
use Masso\Mail\OrderPayment;
use Masso\Behaviors\Facto;
use Masso\Client;
use Masso\Payment;
use Masso\Event;
use Masso\EventEnroll;
use Masso\Log as MassoLog;
use Masso\PaymentDetail;
use Masso\Services\LegacySerializedData;
use Masso\Services\EnrollmentDataResolver;
use Masso\Task;
use Masso\Customer;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class PaymentController extends AdminController
{
    public function index(Request $request)
    {
        $filter  = $request->get('search');
        $status = $request->get('status', null);
        $event = $request->get('event', null);

        // Un pago vinculado a un Customer (Bloque 3) deja name/lastname en
        // NULL en la propia fila -- el dato real vive en customers, y el
        // accessor de Payment lo resuelve solo en PHP, no en SQL. El JOIN +
        // COALESCE deja buscar/filtrar sobre el dato real sin importar de
        // dónde venga; CONCAT además permite calzar "Nombre Apellido"
        // completo aunque estén repartidos en dos columnas.
        $payments = Payment::select('payments.*')
            ->leftJoin('customers', 'customers.id', '=', 'payments.customer_id')
            ->orderBy('payments.created_at', 'DESC');

        if( !is_null($filter) && $filter!='' )
            $payments = $payments->where(function($query) use ($filter) {
                return $query->where('payments.id', 'LIKE', '%'.$filter.'%')
                        ->orWhereRaw('COALESCE(customers.name, payments.name) LIKE ?', ['%'.$filter.'%'])
                        ->orWhereRaw('COALESCE(customers.lastname, payments.lastname) LIKE ?', ['%'.$filter.'%'])
                        ->orWhereRaw("CONCAT(COALESCE(customers.name, payments.name), ' ', COALESCE(customers.lastname, payments.lastname)) LIKE ?", ['%'.$filter.'%']);
            });

        if( !is_null($event) && $event!='')
            $payments = $payments->where(function($query) use ($event) {
                return $query->where('payments.event_id', $event);
            });

        if( !is_null($status) && $status!='')
            $payments = $payments->where(function($query) use ($filter, $status) {
                $status = ($status == 1) ? 'pagado' : 'pending';
                return $query->where('payments.status', 'LIKE', $status);
            });

        $payments = $payments->paginate(20);

        $events = Event::orderBy('name', 'desc')->pluck('name', 'id');

        $title = 'Listado de Pagos';
        return view('admin.general.payments.index', compact('payments', 'title', 'events', 'event') );
    }

    public function searchFolio(Request $request)
    {
        $payment_id = $request->get('folio', null);
        $event = $request->get('event', null);
        $payments = Payment::orderBy('created_at', 'DESC');

        if ( !is_null($payment_id) && $payment_id != '' ):
            $payments = Payment::where('id', 'like', '%'.$payment_id.'%');
        endif;

        $payments = $payments->paginate(20);
        $events = Event::where('status', '1')->orderBy('name', 'desc')->pluck('name', 'id');

        $title = 'Listado de Pagos';
        return view('admin.general.payments.index', compact('payments', 'title', 'events', 'event') );
    }

    public function dte( $id ){

        $payment = Payment::where('status', 'pagado')->where('id', $id)->first();

        if( empty($payment) ):
            \Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
            return \Redirect::back()->withInput();
        endif;


        if( empty($payment->dte) ):
            \Session::flash('error_alert', 'El pago indicado no tiene un DTE asociado.');
            return \Redirect::back()->withInput();
        endif;


        if( empty($payment->document) || !\Storage::exists( $payment->document ) ):

            \Session::flash('error_alert', 'El documento no está almacenado en el servidor, debe obtenerlo a través de FACTO DTE '.$payment->dte.'.');
            return \Redirect::back()->withInput();

        endif;

        return response()->download(storage_path('app/'.$payment->document));

    }
    
    /**
     * Confirmar pago
     */
    public function ticket( Request $request, $id ){
        $payment = Payment::where('status', 'pagado')->where('id', $id)->first();
        $data = $request->all();

        if( isset($data['pending_pay']) && $data['pending_pay'] == 1 ):
            $payment = Payment::where('status', 'pending')->where('id', $id)->first();
            $payment->status = 'pagado';

            try {
                if ($payment->type == 'custom') {
                    // name/lastname/email ya no se copian: al setear
                    // payment_detail_id, EventEnroll las resuelve a través del
                    // payment vinculado (ver EventEnroll::linkedPayment()).
                    $detail = PaymentDetail::where('payment_id', $payment->id)->first();

                    if (!$detail) {
                        Log::info('Ha ocurrido un error [custom]: pago sin PaymentDetail asociado, id=' . $payment->id);
                        \Session::flash('error_alert', 'Ocurrió un error al procesar la operación: no se encontró el detalle del pago. [e:custom-no-detail]');
                        return \Redirect::route('payments.show', $payment->id)->withInput();
                    }

                    $payment_data = LegacySerializedData::safeUnserialize($payment->data);
                    // event_id/ticket_id son columnas reales (en payments y en
                    // el PaymentDetail ya cargado arriba) -- desde que
                    // payments.data dejo de escribirse (Bloque 3), leerlos del
                    // blob siempre daba [] y tiraba "undefined array key",
                    // mostrado como "[e:custom]".
                    $ticket_id = $detail->ticket_id;
                    $event_id = $payment->event_id;

                    $enroll = new EventEnroll();
                    $enroll->event_id = $event_id;
                    $enroll->passport = '';
                    $enroll->phone = '';
                    $enroll->profession = '';
                    $enroll->speciality = '';
                    $enroll->workplace = '';
                    $enroll->city = '';
                    $enroll->country = '';
                    $enroll->ticket_id = $ticket_id;
                    $enroll->data = $payment->data;
                    $enroll->data_json = EnrollmentDataResolver::extraFields($payment_data);
                    $enroll->payment_id = $payment->id;
                    $enroll->payment_detail_id = $detail->id;
                    $enroll->save();

                    $detail->status = PaymentDetail::STATUS_CONFIRMED;
                    $detail->save();

                    $payment->has_inscription = 1;
                }
            } catch (\Throwable $e) {
                Log::info('Ha ocurrido un error [custom]: ');
                Log::info($e);
                Log::info('Fin error');
                \Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente. [e:custom]');
                return \Redirect::route('payments.show', $payment->id)->withInput();
            }
            $payment->save();
            $payment->updateTicketStock();

            PaymentDetail::where('payment_id', $payment->id)
                ->update(['status' => PaymentDetail::STATUS_CONFIRMED]);

            \Session::flash('success_alert', 'El pago ha sido confirmado exitosamente.');
            return \Redirect::route('payments.show', $payment->id)->withInput();
        endif;


        if( empty($payment) ):
            \Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
            return \Redirect::back()->withInput();
        endif;



        if( !empty($payment->dte) ):
            \Session::flash('error_alert', 'El pago indicado ya tiene un DTE asociado.');
            return \Redirect::back()->withInput();
        endif;

        if( empty($data['description']) ):
            \Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
            return \Redirect::back()->withInput();
        endif;

        // $payment->passport ya resuelve vía accessor (Customer si está
        // vinculado) -- antes se leía del blob payments.data, que dejó de
        // escribirse (Bloque 3) y siempre daba "undefined array key" acá.
        $passport = $payment->passport;

        $description = $data['description'];
        $client_name = $payment->name . ' '. $payment->lastname;
        $reference_user = (!empty($data['reference'])) ? ' - '.$data['reference'] : '- Pago Web '.$payment->id;
        $reference   = $client_name . $reference_user;

        $tipo_dte = Facto::getETicketType();
        $client = Facto::getClient();
        $oc_fecha = date('Y-m-d', strtotime($payment->created_at));
        $fecha_emision = date('Y-m-d');

        $cadena_xml = "
            <documento xsi:type='urn:emitir_dte'>
                <encabezado xsi:type='urn:encabezado'>
                    <tipo_dte xsi:type='xsd:string'>".Facto::encoding($tipo_dte)."</tipo_dte>
                    <fecha_emision xsi:type='xsd:date'>".Facto::encoding($fecha_emision)."</fecha_emision>
                    <condiciones_pago xsi:type='xsd:string'><![CDATA[".Facto::encoding(0)."]]></condiciones_pago>
                    <orden_compra_num xsi:type='xsd:string'>".Facto::encoding($payment->id)."</orden_compra_num>
                    <orden_compra_fecha xsi:type='xsd:date'>".Facto::encoding($oc_fecha)."</orden_compra_fecha>
                    <receptor_razon xsi:type='xsd:string'>".Facto::encoding($client_name)."</receptor_razon>
                </encabezado>

                <detalles xsi:type='urn:detalles'>";
                $cadena_xml .= "
                <detalle xsi:type='urn:detalle'>
                    <cantidad xsi:type='xsd:int'>".Facto::encoding(1)."</cantidad>
                    <unidad xsi:type='xsd:string'>unid</unidad>
                    <glosa xsi:type='xsd:string'><![CDATA[".Facto::encoding($description)."]]></glosa>
                    <monto_unitario xsi:type='xsd:decimal'>".Facto::encoding(round($payment->amount,0))."</monto_unitario>
                    <exento_afecto xsi:type='xsd:boolean'>".Facto::encoding(0)."</exento_afecto>
                </detalle>";

                $cadena_xml .= "
                </detalles>

                <referencias xsi:type='urn:referencias'>
                    <referencia xsi:type='urn:referencia'>
                        <docreferencia_tipo>802</docreferencia_tipo>
                        <docreferencia_folio>".Facto::encoding($payment->id)."</docreferencia_folio>
                        <docreferencia_fecha>".Facto::encoding($oc_fecha)."</docreferencia_fecha>
                        <codigo_referencia>5</codigo_referencia>
                        <descripcion>".Facto::encoding($reference)." - RUT: ".$passport."</descripcion>
                    </referencia>
                </referencias>

                <totales xsi:type='urn:totales'>
                    <total_exento xsi:type='xsd:int'>".Facto::encoding(round($payment->amount,0))."</total_exento>
                    <total_afecto xsi:type='xsd:int'>".Facto::encoding( 0 )."</total_afecto>
                    <total_iva xsi:type='xsd:int'>".Facto::encoding( 0 )."</total_iva>
                    <total_final xsi:type='xsd:int'>".Facto::encoding($payment->amount)."</total_final>
                </totales>
            </documento>";

        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        $response = $client->call("emitirDocumento", $cadena_xml);


        if( $data = Facto::checkResponse( $client, $response ) ):

            $payment->dte = 'BE-'.$data['folio'];

            if( !empty($data['dte']) ):
                $contents = file_get_contents($data['dte']);
                $payment->document = 'BE/'.$payment->dte.'.pdf';
                \Storage::put($payment->document, $contents);
            endif;

            $payment->save();
            \Session::flash('success_alert', 'Boleta electrónica emitida satisfactoriamente.');
            return \Redirect::route('payments.show', $payment->id)->withInput();
        else:
            \Session::flash('error_alert', 'Ocurrió un error al emitir la boleta electrónica. Intentelo nuevamente.');
            return \Redirect::route('payments.show', $payment->id)->withInput();
        endif;


    }

    public function processTickets(Request $request) {
        $data = $request->all();
        $payments = $data['payments'];

        if( !isset($payments) || empty($payments) ){
            \Session::flash('error_alert', 'Debe seleccionar al menos un pago para procesar.');
            return \Redirect::back()->withInput();
        }

        // Search if there payment exist in database before create task
        $payments_created = Task::whereIn('object_id', $payments)->pluck('object_id');
        $payments = array_diff($payments, $payments_created->toArray());

        foreach( $payments as $payment_id ) {
            $task = new Task();
            $task->task_name = 'Emitir Boleta';
            $task->controller = 'PaymentController';
            $task->object_id = $payment_id;
            $task->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Emitiendo boletas seleccionadas',
        ]);
    }

	public function create( $client )
    {
        $client = Client::where('id', $client)->firstOrFail();
        $title = 'Crear Nuevo Pago';

        return view('admin.general.payments.create', compact('client','title'));
    }

	public function store( PaymentStoreRequest $request, $client )
    {

    	$data = $request->all();
        $data['status'] = 'pendiente';
        $data['client_id'] = $client;

    	if( $payment = Payment::create( $data ) ):
            MassoLog::create(['area'=>'General', 'module'=>'Pagos', 'action'=>'Creó pago folio '.$payment->id, 'user_id'=>\Auth::user()->id]);
            \Mail::to($payment->client->email)->send(new OrderPayment($payment));
    		\Session::flash('success_alert', 'El pago ha sido generado exitosamente. Se envió notificación a cliente.');
            return \Redirect::route('payments.index');
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }

	public function show($id)
    {
        $payment = Payment::where('id', $id)->first();

        if( empty($payment) )
            abort(404);

        if( isset($_GET['send-mail']) ):

            \Mail::to($payment->client->email)->send(new OrderPayment($payment));
            \Session::flash('success_alert', 'El correo ha sido reenviado al cliente '.$payment->client->mail.'.');
            return \Redirect::back()->withInput();
        endif;


        // Participantes cargados por Excel (pagos grupales)
        $participantsList = [];
        $participantsDownloadUrl = null;
        $participantsCount = null;

        try {
            // Columnas reales (antes vivían solo en el blob/data_json).
            $participantsDownloadUrl = $payment->participants_excel_file;
            $participantsCount = $payment->participants_count;

            if ($participantsDownloadUrl) {
                $absolutePath = public_path(ltrim($participantsDownloadUrl, '/'));

                if (file_exists($absolutePath)) {
                    // Lee la primera hoja con encabezados en la fila 1
                    $sheets = Excel::toArray(
                        new class implements ToArray, WithHeadingRow {
                            public function array(array $rows)
                            {
                                return $rows;
                            }

                            public function headingRow(): int
                            {
                                return 1;
                            }
                        },
                        $absolutePath
                    );

                    $participantsList = $sheets[0] ?? [];
                }
            }
        } catch (\Throwable $e) {
            // Silencioso: no bloquea la vista si el Excel falla al leerse
            $participantsList = [];
        }


        $title = 'Pago Folio #'.$payment->id;
        return view('admin.general.payments.show', compact('payment','title','participantsList','participantsDownloadUrl','participantsCount'));
    }

    public function destroy($id)
    {
        $payment = Payment::where('status', 'pending')->where('id', $id)->first();

        if( !empty($payment) ):
            MassoLog::create(['area'=>'General', 'module'=>'Pagos', 'action'=>'Eliminó pago '.$payment->id, 'user_id'=>\Auth::user()->id]);

            $payment->save();
            $payment->delete();
            \Session::flash('success_alert', 'Se ha eliminado el pago folio #'.$payment->id.'.');
        endif;

        return \Redirect::back()->withInput();
    }

    /**
     * Campos "default" que, desde el ítem 3.3c, se resuelven vía el
     * Customer vinculado en vez de la columna propia del pago -- si se
     * editan acá para un pago ya vinculado, hay que escribir en el
     * Customer (la columna del pago ya no se lee).
     */
    private const CUSTOMER_RESOLVED_FIELDS = [
        'name', 'lastname', 'email', 'rut', 'passport', 'gender',
        'nationality_country_id', 'city_id', 'country_id', 'custom_city',
    ];

    public function updateValue(Request $request, $id)
    {
        $payment = Payment::where('status', 'pending')->where('id', $id)->first();

        if (!empty($payment)) {

            $field = $request->field;

            if (in_array($field, self::CUSTOMER_RESOLVED_FIELDS) && !empty($payment->customer_id)) {
                $value = $field === 'email' ? mb_strtolower(trim($request->value)) : $request->value;

                if ($field === 'email' && Customer::where('email', $value)->where('id', '!=', $payment->customer_id)->exists()) {
                    \Session::flash('error_alert', 'Ese correo ya pertenece a otro cliente.');
                    return \Redirect::back()->withInput();
                }

                $payment->customer->update([$field => $value]);
                \Session::flash('success_alert', 'Dato actualizado (se guardó en el perfil del cliente).');
                return \Redirect::back()->withInput();
            }

            $updateData = [
                $field => $request->value
            ];

            if ($field === 'amount') {
                $updateData['coupon_id'] = null;
                $updateData['discount_amount'] = null;
                $updateData['discount_percentage'] = null;
            }

            $payment->update($updateData);

            \Session::flash('success_alert', 'Dato actualizado');
        }

        return \Redirect::back()->withInput();
    }
}
