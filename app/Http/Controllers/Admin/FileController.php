<?php

namespace Masso\Http\Controllers\Admin;
use Masso\Http\Requests\EventFileUpdateRequest;
use Masso\Http\Requests\EventFileStoreRequest;
use Illuminate\Http\Request;
use Masso\Behaviors\FileBehavior;
use Masso\EventFile;
use Masso\Event;
use Masso\Log;

class FileController extends AdminController
{


    public function index(Request $request, $event)
    {
        $event = Event::where('id',$event)->firstOrFail();
        $filter = $request->get('search', false);
        $files = $event->files()->orderBy('name', 'ASC');

        if( !empty($filter) )
            $files = $files->where('name', 'LIKE', '%'.$filter.'%');

        $files = $files->paginate(20);
        $title = 'Listado de Documentos Evento '.$event->name;
        return view('admin.general.files.index', compact('files', 'event', 'title') );  
    }


	public function create( $event )
    {
        $event = Event::where('id',$event)->firstOrFail();
        $title = 'Crear Nuevo Evento Expirado';
        return view('admin.general.files.create', compact('title', 'event'));
    }


	public function store( EventFileStoreRequest $request, $event )
    {
        $event = Event::where('id',$event)->firstOrFail();
    	$data = $request->all();

        if( $request->hasFile('file') ):
            $data['file'] = FileBehavior::upload( 'file', 'documents/', $request );
            $data['extension'] = $request->file->getClientOriginalExtension();
        endif;

    	if( $event->files()->create( $data ) ):
            Log::create(['area'=>'Eventos', 'module'=>'Documentos', 'action'=>'Creó Documento '.$data['name'].' a Evento '.$event->name, 'user_id'=>\Auth::user()->id]);
    		\Session::flash('success_alert', 'El documento ha sido creado exitosamente.');
            return \Redirect::route('files.index', $event->id);
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }



	public function edit($event, $file)
    {
        $file  = EventFile::where('event_id', $event)->where('id', $file)->firstOrFail();
        $event = Event::where('id', $event)->firstOrFail();

        if( empty($file) )
            abort(404);

        $title = 'Editar Documento '.$file->name;
        return view('admin.general.files.edit', compact('event','file','title'));
    }


	public function update( EventFileUpdateRequest $request, $event, $file )
    {
        
        $file  = EventFile::where('event_id', $event)->where('id', $file)->firstOrFail();
        $event = Event::where('id',$event)->firstOrFail();
    	$data = $request->only('name');

        if( $request->hasFile('file') )
            $data['file'] = FileBehavior::upload( 'file', 'documents/', $request );

        $file->fill( $data );

    	if( $file->save() ):
            Log::create(['area'=>'Eventos', 'module'=>'Documentos', 'action'=>'Editó documento '.$file->name.' de Evento '.$event->name, 'user_id'=>\Auth::user()->id]);
    		\Session::flash('success_alert', 'El documento ha sido actualizado exitosamente.');
            return \Redirect::route('files.index', $event->id);
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }


    public function destroy($event, $file)
    {
        $file  = EventFile::where('event_id', $event)->where('id', $file)->firstOrFail();

        if( !empty($file) ):
            Log::create(['area'=>'Eventos', 'module'=>'Documentos', 'action'=>'Eliminó documento '.$file->name, 'user_id'=>\Auth::user()->id]);
            $file->delete();
        endif;

		\Session::flash('success_alert', 'El documento ha sido eliminado exitosamente.');
        return \Redirect::back()->withInput();
    }
}