<?php

namespace Masso\Http\Controllers\Admin;
use Masso\Http\Requests\EventEnrollStoreRequest;
use Masso\Http\Requests\EventStoreRequest;
use Illuminate\Http\Request;
use Masso\Behaviors\FileBehavior;
use Masso\Event;
use Masso\Log;

class EnrollController extends AdminController
{
    private $field_private = [
        '_token',
        'name',
        'lastname',
        'passport',
        'email',
        'ticket',
        'payment',
        'check',
        'ids',
        'amount',
        'available',
        'event_id'
    ];

    public function index(Request $request, $event)
    {
        $event = Event::where('id', $event)->first();
        $assistants = $event->assistants()->orderBy('id', 'DESC');

        if( !empty($filter) )
            $assistants = $assistants->where('name', 'LIKE', '%'.$filter.'%')->limit(1000);

        if( isset($_GET['download'])):
            $_assistant = $assistants->get();
            $assistants = [];
            $invoices = [];
            $descriptions_invoices = [];

            foreach( $_assistant as $a ):
                // get data payment
                $data_payment = [
                    'Folio'=>'',
                    'Fecha de Pago' => '',
                    'Total Pago' => '',
                    'Dte' => '',
                    'Documento' => '',
                    'Forma Pago' => '',
                    'Tipo de Pago' => '',
                    'Tarjeta' => '',
                    'Cod. Autorización' => ''
                ];

                $asistant_payment = $a->payment()->first();
                if ($asistant_payment!==null && !in_array($asistant_payment->id, $invoices)) {
                    $invoices[] = $asistant_payment->id;
                    foreach($_assistant as $assis):
                        if ($asistant_payment->id == $assis->payment_id):
                            $descriptions_invoices[$asistant_payment->id][] = $assis->ticket->name;
                        endif;
                    endforeach;

                    if ($asistant_payment!==null) {
                        $data_payment_ = [
                            'Folio'=> '' . $asistant_payment->id,
                            'Fecha de Pago' => date('d-m-Y H:i', strtotime($asistant_payment->created_at)),
                            'Total Pago' => '' . $asistant_payment->amount,
                            'Dte' => $asistant_payment->dte,
                            'Documento' => $asistant_payment->dte !='' ? route('payments.dte', $asistant_payment->id) : '',
                            'Forma Pago' => $asistant_payment->managment,
                            'Tipo de Pago' => '',
                            'Tarjeta' => '',
                            'Cod. Autorización' => ''
                        ];

                        $payment_transaction = $a->payment()->first()->transactions()->first();

                        if ($payment_transaction !== null) {
                            $data_payment_['Tipo de Pago'] = ($payment_transaction->payment_type == 'VN' ? 'Débito' : 'Crédito');
                            $data_payment_['Tarjeta'] = $payment_transaction->card_number;
                            $data_payment_['Cod. Autorización'] = $payment_transaction->auth_code;
                        }

                        $data_payment = array_merge($data_payment, $data_payment_);
                    }

                    $enr = [
                        'Evento'=>$event->name,
                        'Ticket'=>implode('  ||  ', $descriptions_invoices[$asistant_payment->id]),
                        // 'Ticket'=>$a->ticket->name,
                        'Fecha Inscripcion'=>date('d-m-Y H:i', strtotime($a->created_at)),
                        'Nombre'=>$a->name,
                        'Apellido'=>$a->lastname,
                        'Cédula Identidad / Pasaporte'=>$a->passport,
                        'Email'=>$a->email
                    ];

                    try {
                        $additional = @unserialize($a->data);
                        $_add = [];

                        if( !is_array($additional) )
                            $additional = @unserialize($additional);

                    } catch (\Exception $e) {
                        $additional = [];
                    }

                    if ( $additional > 0 ) {
                        foreach ($additional as $key => $add):
                            if (!in_array($key, ['status', 'type', 'managment', 'has_inscription', 'ticket_id', 'billing_method', 'invoice_data', 'rut', 'city_id', 'nationality_country_id', 'country_id', 'region_id', 'custom_city'])):
                                if (!in_array($key, $this->field_private)):
                                    // $enr[$key] = $add;
                                    $_add[$key] = $add;
                                endif;
                            endif;
                        endforeach;
                    }

                    $assistants[] = array_merge($enr, $data_payment, $_add);
                }
            endforeach;


            $originalReporting = error_reporting();
            // Ignorar errores deprecated (como el de las llaves {})
            error_reporting(0);

            try {
                \Excel::create('inscritos-'.$event->name, function($excel) use ($assistants){
                    $excel->sheet('Inscritos', function($sheet) use ($assistants) {
                        $sheet->fromArray($assistants);
                    });
                })->export('xls');
            }
            catch(\Exception $e){
                throw $e;
            } finally {
                // Restaurar nivel original
                error_reporting($originalReporting);
            }   
        endif;

        $assistants = $assistants->get();

        $sum_payments = 0;
        $folio_total = [];

        foreach($assistants as $key => $assistant):
            try {
                if (!in_array($assistant->payment()->first()->id, $folio_total)) {
                    $folio_total[$assistant->payment()->first()->id] = $assistant->payment()->first()->amount;
                }
            } catch (\Exception $e) {
                $sum_payments += 0;
            }
        endforeach;

        foreach($folio_total as $key => $value) {
            $sum_payments += $value;
        }

        $total_format = number_format($sum_payments,0,',','.');
        $title = 'Listado de Asistentes';

        return view('admin.general.enroll.index', compact('event', 'assistants', 'title', 'total_format'));
    }


	public function create( $event )
    {
        $event = Event::where('id', $event)->firstOrFail();
        $title = 'Crear Asistente';

        return view('admin.general.enroll.create', compact('title', 'event'));
    }


	public function store( EventEnrollStoreRequest $request, $event )
    {
        $event = Event::where('id', $event)->firstOrFail();
    	$data = $request->all();


    	if( $event->assistants()->create( $data ) ):
            Log::create(['area'=>'Inscritos', 'module'=>'Asistentes', 'action'=>'Creó asistente '.$data['name'], 'user_id'=>\Auth::user()->id]);
    		\Session::flash('success_alert', 'El asistente ha sido inscrito exitosamente.');
            return \Redirect::route('enrolls.index', $event->id);
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }



	public function show($event, $id)
    {
        $event = Event::where('id', $event)->firstOrFail();
        $assistant = $event->assistants()->where('id', $id)->first();

        if( empty($assistant) )
            abort(404);

        $assistant->processData();
        $title = 'Ficha Asistente '.$event->name;
        return view('admin.general.enroll.show', compact('event','title','assistant'));
    }


    public function destroy($id)
    {

        $events = Event::find($id);

        if( !empty($events) ):
            Log::create(['area'=>'Eventos', 'module'=>'Eventos', 'action'=>'Eliminó evento '.$events->name, 'user_id'=>\Auth::user()->id]);
            $events->delete();
        endif;

		\Session::flash('success_alert', 'El evento ha sido eliminado exitosamente.');
        return \Redirect::back()->withInput();
    }
}
