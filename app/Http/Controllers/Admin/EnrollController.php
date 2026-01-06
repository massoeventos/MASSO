<?php

namespace Masso\Http\Controllers\Admin;
use Masso\Http\Requests\EventEnrollStoreRequest;
use Masso\Http\Requests\EventStoreRequest;
use Illuminate\Http\Request;
use Masso\Behaviors\FileBehavior;
use Masso\Event;
use Masso\Log;
use Masso\Exports\EnrollmentsExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelWriter;

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

    private function upperValue($value)
    {
        if ($value === null) {
            return '';
        }

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if (!is_string($value)) {
            $value = (string) $value;
        }

        // Do not uppercase file paths/URLs (keeps '/files/..' intact)
        if (stripos($value, '/files') !== false) {
            return $value;
        }

        return function_exists('mb_strtoupper')
            ? mb_strtoupper($value, 'UTF-8')
            : strtoupper($value);
    }

    private function upperRow(array $row)
    {
        $out = [];
        foreach ($row as $key => $value) {
            $upperKey = is_string($key) ? $this->upperValue($key) : $key;
            $out[$upperKey] = $this->upperValue($value);
        }
        return $out;
    }

    public function index(Request $request, $event)
    {
        $event = Event::where('id', $event)->firstOrFail();
        $assistants = $event->assistants()->orderBy('created_at', 'DESC')->orderBy('id', 'DESC');

        if( !empty($filter) ){
            $assistants = $assistants->where('name', 'LIKE', '%'.$filter.'%')->limit(1000);
        }

        if( isset($_GET['download'])){
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
                    'Cupón aplicado' => '',
                    'Dte' => '',
                    'Documento' => '',
                    'Forma Pago' => '',
                    'Tipo de Pago' => '',
                    'Tarjeta' => '',
                    'Cod. Autorización' => '',
                    'GÉNERO' => ''
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
                            'Folio' => '' . $asistant_payment->id,
                            'Fecha de Pago' => date('d-m-Y H:i', strtotime($asistant_payment->created_at)),
                            'Total Pago' => '' . $asistant_payment->amount,
                            'Cupón aplicado' => $asistant_payment->coupon ? $asistant_payment->coupon->code : '',
                            'Dte' => $asistant_payment->dte,
                            'Documento' => $asistant_payment->dte != '' ? route('payments.dte', $asistant_payment->id) : '',
                            'Forma Pago' => $asistant_payment->managment,
                            'Tipo de Pago' => '',
                            'Tarjeta' => '',
                            'Cod. Autorización' => '',
                            'Método de facturación' => $asistant_payment->billing_method_print,
                            'FACT. Razón social' => $asistant_payment->getInvoiceDataField('business_name'),
                            'FACT. RUT' => $asistant_payment->getInvoiceDataField('rut'),
                            'FACT. Giro' => $asistant_payment->getInvoiceDataField('business_activity'),
                            'FACT. Dirección' => $asistant_payment->getInvoiceDataField('address'),
                            'FACT. Ciudad' => $asistant_payment->getInvoiceDataField('city'),
                            'FACT. Teléfono' => $asistant_payment->getInvoiceDataField('phone'),
                            'FACT. Observación' => $asistant_payment->getInvoiceDataField('note'),
                            'GÉNERO' => $asistant_payment->getGenderLabel(),
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
                        'RUT'=> $a->rut_print,
                        'DNI / Pasaporte'=>$a->passport,
                        'Email'=>$a->email,
                        'Nacionalidad' => $a->nationalityCountry ? $a->nationalityCountry->name : null,
                        'País' => $a->deepCountry ? $a->deepCountry->name : null,
                        'Región' => $a->cityRel ? $a->cityRel->region->name : null,
                        'Ciudad' => $a->cityRel ? $a->cityRel->name : $a->custom_city,
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
                            if (!in_array($key, ['status', 'type', 'managment', 'has_inscription', 'ticket_id', 'billing_method', 'invoice_data', 'rut', 'city_id', 'nationality_country_id', 'country_id', 'region_id', 'custom_city' , 'description', 'gender'])):
                                if (!in_array($key, $this->field_private)):
                                    $_add[$key] = $add;
                                endif;
                            endif;
                        endforeach;
                    }

                    $assistants[] = array_merge($enr, $data_payment, $_add);
                }
            endforeach;
            
            // Paso 1: Capturar el orden original de claves del primer asistente
            $baseKeys = array_keys(reset($assistants));

            // Paso 2: Reunir todas las claves posibles
            $allKeys = $baseKeys;

            foreach ($assistants as $row) {
                foreach (array_keys($row) as $key) {
                    if (!in_array($key, $allKeys)) {
                        $allKeys[] = $key; // agrega nuevos campos al final
                    }
                }
            }

            // Paso 3: Normalizar filas con todas las claves
            $normalized = [];

            foreach ($assistants as $row) {
                $normalized[] = array_merge(array_fill_keys($allKeys, ''), $row);
            }

            // Convert headers + values to uppercase for the downloaded spreadsheet
            $normalized = array_map(function ($row) {
                return $this->upperRow($row);
            }, $normalized);

            
            // $normalized = $assistants;
            // dd($normalized);


            $headings = array_keys($normalized[0] ?? []);
            $rowsForExport = array_map('array_values', $normalized);

            return Excel::download(
                new EnrollmentsExport($rowsForExport, $headings),
                'inscritos-'.$event->name.'.xls',
                ExcelWriter::XLS
            );

        }

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
