<?php

namespace Masso\Http\Controllers\Admin;

use Masso\Http\Requests\ClientStoreRequest;
use Illuminate\Http\Request;
use Masso\Http\Requests\ClientUpdateRequest;
use Masso\EventEnroll;
use Masso\Log;

class ClientController extends AdminController
{


    public function index(Request $request)
    {
        $filter = $request->get('search', false);
        $clients = EventEnroll::groupBy('passport')->orderBy('name','desc');

        if( !empty($filter) )
            $clients = $clients->where(function($query) use ($filter) {
                return $query->where('name', 'LIKE', '%'.$filter.'%')
                        ->orWhere('email', 'LIKE', '%'.$filter.'%')
                        ->orWhere('passport', 'LIKE', '%'.$filter.'%');
            });

        if( isset($_GET['download'])):
            $clients = $clients->get();
            $assistants = [];

            foreach( $clients as $a )
                $assistants[] = [ 'Nombre'=>$a->name, 'Apellido'=>$a->lastname, 'Email'=>$a->email, 'Telèfono'=>$a->phone, 'Profesiòn' => $a->profession, 'Especialidad' => $a->speciality, 'Lugar de Trabajo' => $a->workplace, 'Ciudad'=>$a->city, 'Paìs'=>$a->country, 'Entrada'=>$a->ticket->name, 'Último Evento'=>$a->event->name
                ];


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
