<?php

namespace Masso\Http\Controllers\Admin;
use Masso\Http\Requests\MemberStoreRequest;
use Illuminate\Http\Request;
use Masso\Behaviors\FileBehavior;
use Masso\TeamMember;
use Masso\Log;

class TeamController extends AdminController
{


    public function index(Request $request )
    {
        $members = TeamMember::orderBy('name', 'ASC');

        if( !empty($filter) )
            $files = $files->where('name', 'LIKE', '%'.$filter.'%');

        $members = $members->paginate(20);
        $title = 'Miembros del Equipo';
        return view('admin.general.members.index', compact('members', 'title') );  
    }


	public function create()
    {
        $title = 'Crear Nuevo Miembro del Equipo';
        return view('admin.general.members.create', compact('title'));
    }


	public function store( MemberStoreRequest $request )
    {
    	$data = $request->all();

        if( $request->hasFile('image') ):
            $data['image'] = FileBehavior::upload( 'image', 'members/', $request );
        endif;

    	if( TeamMember::create( $data ) ):
            Log::create(['area'=>'Team', 'module'=>'Miembros', 'action'=>'Creó Miembro '.$data['name'], 'user_id'=>\Auth::user()->id]);
    		\Session::flash('success_alert', 'El miembro ha sido creado exitosamente.');
            return \Redirect::route('team.index');
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }



	public function edit($id)
    {
        $member  = TeamMember::where('id', $id)->firstOrFail();

        if( empty($member) )
            abort(404);

        $title = 'Editar Miembro '.$member->name;
        return view('admin.general.members.edit', compact('member','title'));
    }


	public function update( MemberStoreRequest $request, $id )
    {
        
        $member  = TeamMember::where('id', $id)->firstOrFail();
    	$data = $request->only('name', 'description');

        if( $request->hasFile('image') )
            $data['image'] = FileBehavior::upload( 'image', 'members/', $request );

        $member->fill( $data );

    	if( $member->save() ):
            Log::create(['area'=>'Team', 'module'=>'Miembros', 'action'=>'Editó Miembro '.$member->name, 'user_id'=>\Auth::user()->id]);
    		\Session::flash('success_alert', 'El miembro ha sido actualizado exitosamente.');
            return \Redirect::route('team.index');
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }


    public function destroy($id)
    {

        $member  = TeamMember::where('id', $id)->firstOrFail();

        if( !empty($member) ):
            Log::create(['area'=>'Team', 'module'=>'Miembros', 'action'=>'Eliminó miembro equipo '.$member->name, 'user_id'=>\Auth::user()->id]);
            $member->delete();
        endif;

		\Session::flash('success_alert', 'El miembro ha sido eliminado exitosamente.');
        return \Redirect::back()->withInput();
    }
}