<?php

namespace Masso\Http\Controllers\Admin;
use Masso\Http\Requests\EventUpdateRequest;
use Masso\Http\Requests\EventStoreRequest;
use Illuminate\Http\Request;
use Masso\Behaviors\FileBehavior;
use Masso\Event;
use Masso\Log;
use Masso\EventExpired;

class EventController extends AdminController
{


    public function index(Request $request)
    {
        $filter = $request->get('search', false);
        $events = Event::orderBy('id', 'DESC');

        if( !empty($filter) )
            $events = $events->where('name', 'LIKE', '%'.$filter.'%');

        $events = $events->paginate(20);
        $title = 'Listado de Eventos';
        return view('admin.general.events.index', compact('events', 'title') );
    }


	public function create()
    {
        $title = 'Crear Nuevo Evento';
        return view('admin.general.events.create', compact('title'));
    }


	public function store( EventStoreRequest $request )
    {

    	$data = $request->all();

        if( $request->hasFile('photo') )
            $data['photo'] = FileBehavior::upload( 'photo', 'images/events/', $request );

    	if( $event = Event::create( $data ) ):

            if( !empty($data['tickets']) )
                foreach( $data['tickets'] as $ticket )
                    $event->tickets()->create($ticket);

            if( !empty($data['inputs']) )
                foreach( $data['inputs'] as $key => $input )
                    $event->inputs()->updateOrCreate(['id'=>$key], $input);

            Log::create(['area'=>'Eventos', 'module'=>'Eventos', 'action'=>'Creó Evento '.$data['name'], 'user_id'=>\Auth::user()->id]);
    		\Session::flash('success_alert', 'El evento ha sido creado exitosamente.');
            return \Redirect::route('events.index');
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }

	public function edit($id)
    {

        $event = Event::where('id', $id)->first();

        if( empty($event) )
            abort(404);

        $title = 'Editar Evento '.$event->name;

        return view('admin.general.events.edit', compact('event','title'));
    }


	public function update( EventUpdateRequest $request, $id )
    {
    	$event = Event::findOrFail($id);
    	$data = $request->only(
    	    'name',
            'location',
            'date_init',
            'date_finish',
            'status',
            'description',
            'description_eng',
            'tickets',
            'inputs',
            'organize',
            'isUC',
            'is_multiple_selection_ticket',
            'max_selection_ticket',
            'terms_and_conditions',
            'terms_and_conditions_eng'
        );

        if( $request->hasFile('photo') )
            $data['photo'] = FileBehavior::upload( 'photo', 'images/events/', $request );

        $event->fill( $data );
    	if( $event->save() ):

            $event->tickets()->delete();
            if( !empty($data['tickets']) )
                foreach( $data['tickets'] as $key => $ticket )
                    $event->tickets()->updateOrCreate(['id'=>$key], $ticket);

            $event->inputs()->delete();
            if( !empty($data['inputs']) )
                foreach( $data['inputs'] as $key => $input )
                    $event->inputs()->updateOrCreate(['id'=>$key], $input);

            Log::create(['area'=>'Eventos', 'module'=>'Eventos', 'action'=>'Editó evento '.$event->name, 'user_id'=>\Auth::user()->id]);
    		\Session::flash('success_alert', 'El evento ha sido actualizado exitosamente.');
            return \Redirect::route('events.index');
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }


    public function destroy($id)
    {

        $events = Event::find($id);

        if( $events->status != 2 ):

            EventExpired::create(['name'=>$events->name, 'date_init'=>$events->date_init, 'date_finish'=>$events->date_finish, 'photo'=>$events->photo, 'location'=>$events->location]);

            $events->status = 2;
            Log::create(['area'=>'Eventos', 'module'=>'Eventos', 'action'=>'Archivó evento '.$events->name, 'user_id'=>\Auth::user()->id]);
            $events->save();
            \Session::flash('success_alert', 'El evento ha sido archivado exitosamente.');
            return \Redirect::back()->withInput();
        endif;

        if( !empty($events) ):
            Log::create(['area'=>'Eventos', 'module'=>'Eventos', 'action'=>'Eliminó evento '.$events->name, 'user_id'=>\Auth::user()->id]);
            $events->delete();
        endif;

		\Session::flash('success_alert', 'El evento ha sido eliminado exitosamente.');
        return \Redirect::back()->withInput();
    }
}
