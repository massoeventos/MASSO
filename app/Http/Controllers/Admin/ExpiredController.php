<?php

namespace Masso\Http\Controllers\Admin;
use Masso\Http\Requests\EventExpiredUpdateRequest;
use Masso\Http\Requests\EventExpiredStoreRequest;
use Illuminate\Http\Request;
use Masso\Behaviors\FileBehavior;
use Masso\EventExpired;
use Masso\Log;

class ExpiredController extends AdminController
{


    public function index(Request $request)
    {
        $filter = $request->get('search', false);
        $expired = EventExpired::orderBy('date_finish', 'DESC');

        if( !empty($filter) )
            $expired = $expired->where('name', 'LIKE', '%'.$filter.'%');

        $expired = $expired->paginate(20);
        $title = 'Listado de Eventos Expirados';
        return view('admin.general.expired.index', compact('expired', 'title') );  
    }


	public function create()
    {
        $title = 'Crear Nuevo Evento Expirado';
        return view('admin.general.expired.create', compact('title'));
    }


	public function store( EventExpiredStoreRequest $request )
    {

    	$data = $request->all();

        if( $request->hasFile('photo') )
            $data['photo'] = FileBehavior::upload( 'photo', 'images/events/', $request );

    	if( EventExpired::create( $data ) ):
            Log::create(['area'=>'Eventos', 'module'=>'Expirados', 'action'=>'Creó Evento Expirado '.$data['name'], 'user_id'=>\Auth::user()->id]);
    		\Session::flash('success_alert', 'El evento expirado ha sido creado exitosamente.');
            return \Redirect::route('expired.index');
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }



	public function edit($id)
    {
        $expired = EventExpired::where('id', $id)->first();

        if( empty($expired) )
            abort(404);

        $title = 'Editar Evento Expirado '.$expired->name;
        return view('admin.general.expired.edit', compact('expired','title'));
    }


	public function update( EventExpiredUpdateRequest $request, $id )
    {

    	$expired = EventExpired::findOrFail($id);
    	$data = $request->only('name', 'date_init', 'date_finish', 'location');

        if( $request->hasFile('photo') )
            $data['photo'] = FileBehavior::upload( 'photo', 'images/events/', $request );

        $expired->fill( $data );

    	if( $expired->save() ):
            Log::create(['area'=>'Eventos', 'module'=>'Expirados', 'action'=>'Editó evento expirado '.$expired->name, 'user_id'=>\Auth::user()->id]);
    		\Session::flash('success_alert', 'El evento ha sido actualizado exitosamente.');
            return \Redirect::route('expired.index');
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }


    public function destroy($id)
    {

        $expired = EventExpired::find($id);

        if( !empty($expired) ):
            Log::create(['area'=>'Eventos', 'module'=>'Expirados', 'action'=>'Eliminó evento expirado '.$expired->name, 'user_id'=>\Auth::user()->id]);
            $expired->delete();
        endif;

		\Session::flash('success_alert', 'El evento ha sido eliminado exitosamente.');
        return \Redirect::back()->withInput();
    }
}