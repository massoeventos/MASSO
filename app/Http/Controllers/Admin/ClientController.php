<?php

namespace Masso\Http\Controllers\Admin;

use Masso\Http\Requests\ClientStoreRequest;
use Illuminate\Http\Request;
use Masso\Http\Requests\ClientUpdateRequest;
use Masso\EventEnroll;
use Masso\Log;
use Masso\Services\LegacySerializedData;
use Masso\Services\EnrollmentDataResolver;
use Illuminate\Support\Facades\DB;

class ClientController extends AdminController
{


    public function index(Request $request)
    {
        $filter = $request->get('search', false);

        // Igual que en los accessors de EventEnroll/Payment: un asistente
        // que viene de un pago real deja su propio name/email/passport en
        // NULL -- el dato se resuelve vía payment_detail -> payment (y ese,
        // vía customer si corresponde). Sin estos JOIN, agrupar/ordenar/
        // filtrar en SQL directo sobre las columnas de events_enroll hacía
        // que todo asistente nuevo cayera en un solo grupo NULL y nunca
        // apareciera al buscar.
        $clients = EventEnroll::select('events_enroll.*')
            ->leftJoin('payments_detail', 'payments_detail.id', '=', 'events_enroll.payment_detail_id')
            ->leftJoin('payments', 'payments.id', '=', 'payments_detail.payment_id')
            ->leftJoin('customers', 'customers.id', '=', 'payments.customer_id')
            ->groupBy(DB::raw('COALESCE(customers.passport, payments.passport, events_enroll.passport)'))
            ->orderByRaw('COALESCE(customers.name, payments.name, events_enroll.name) desc');

        if( !empty($filter) )
            $clients = $clients->where(function($query) use ($filter) {
                // name/lastname son columnas separadas -- buscar solo "name"
                // nunca iba a calzar con un nombre completo tipo "Zulema
                // Guerra" (lastname quedaba fuera de la comparación). Se
                // agrega lastname y el nombre completo concatenado.
                return $query->whereRaw('COALESCE(customers.name, payments.name, events_enroll.name) LIKE ?', ['%'.$filter.'%'])
                        ->orWhereRaw('COALESCE(customers.lastname, payments.lastname, events_enroll.lastname) LIKE ?', ['%'.$filter.'%'])
                        ->orWhereRaw("CONCAT(COALESCE(customers.name, payments.name, events_enroll.name), ' ', COALESCE(customers.lastname, payments.lastname, events_enroll.lastname)) LIKE ?", ['%'.$filter.'%'])
                        ->orWhereRaw('COALESCE(customers.email, payments.email, events_enroll.email) LIKE ?', ['%'.$filter.'%'])
                        ->orWhereRaw('COALESCE(customers.passport, payments.passport, events_enroll.passport) LIKE ?', ['%'.$filter.'%']);
            });

        if( isset($_GET['download'])):
            $clients = $clients->get();
            $assistants = [];

            foreach( $clients as $a ) {
                // Estas columnas ya no se llenan desde el formulario público
                // actual (quedan vacías en events_enroll); si el registro es
                // viejo, el mismo dato puede seguir existiendo dentro del
                // blob legado. La columna real siempre gana cuando tiene valor.
                // No se puede usar data_json acá: a partir de este ítem solo
                // guarda los campos SIN columna real (ver
                // EnrollmentDataResolver::KNOWN_KEYS), y phone/profession/...
                // son justamente claves conocidas — hay que leer el blob
                // completo original.
                $raw = LegacySerializedData::safeUnserialize($a->data);

                $assistants[] = [
                    'Nombre' => $a->name,
                    'Apellido' => $a->lastname,
                    'Email' => $a->email,
                    'Telèfono' => EnrollmentDataResolver::columnOrFallback($a->phone, $raw, 'phone'),
                    'Profesiòn' => EnrollmentDataResolver::columnOrFallback($a->profession, $raw, 'profession'),
                    'Especialidad' => EnrollmentDataResolver::columnOrFallback($a->speciality, $raw, 'speciality'),
                    'Lugar de Trabajo' => EnrollmentDataResolver::columnOrFallback($a->workplace, $raw, 'workplace'),
                    'Ciudad' => EnrollmentDataResolver::columnOrFallback($a->city, $raw, 'city'),
                    'Paìs' => EnrollmentDataResolver::columnOrFallback($a->country, $raw, 'country'),
                    'Entrada' => $a->ticket->name,
                    'Último Evento' => $a->event->name,
                ];
            }


            \Excel::create('historico-inscritos', function($excel) use ($assistants){

                $excel->sheet('Total Historico', function($sheet) use ($assistants) {
                    $sheet->fromArray($assistants);
                });
            })->export('xls');
        endif;

        $clients = $clients->paginate(20);
        $title = 'Listado de Inscritos Histórico';
        return view('admin.general.clients.index', compact('clients', 'title') );
    }

	public function create()
    {
        $title = 'Crear Nuevo Inscrito';
        return view('admin.general.clients.create', compact('title'));
    }


	public function store( ClientStoreRequest $request )
    {

    	$data = $request->all();

    	if( Client::create( $data ) ):
            Log::create(['area'=>'General', 'module'=>'Cliente', 'action'=>'Creó cliente '.$data['rut'], 'user_id'=>\Auth::user()->id]);
    		\Session::flash('success_alert', 'El cliente ha sido creado exitosamente.');
            return \Redirect::route('clients.index');
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }



	public function edit($id)
    {
        $user = Client::where('id', $id)->first();

        if( empty($user) )
            abort(404);

        $title = 'Editar Cliente: '.$user->name;
        return view('admin.general.clients.edit', compact('user','title'));
    }


	public function update( ClientUpdateRequest $request, $id )
    {

    	$user = Client::findOrFail($id);
    	$data = $request->only('name', 'rut', 'email', 'country', 'comments');
    	$user->fill( $data );

    	if( $user->save() ):
            Log::create(['area'=>'General', 'module'=>'Cliente', 'action'=>'Editó cliente '.$user->rut, 'user_id'=>\Auth::user()->id]);
    		\Session::flash('success_alert', 'El cliente ha sido actualizado exitosamente.');
            return \Redirect::route('clients.index');
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }


    public function destroy($id)
    {

        $user = Client::find($id);

        if( !empty($user) ):
            Log::create(['area'=>'General', 'module'=>'Cliente', 'action'=>'Eliminó cliente '.$user->rut, 'user_id'=>\Auth::user()->id]);
            $user->email = time().'_'.$user->email;
            $user->rut = time().'_'.$user->rut;
            $user->save();
            $user->delete();
        endif;

		\Session::flash('success_alert', 'El cliente ha sido eliminado exitosamente.');
        return \Redirect::back()->withInput();
    }
}
