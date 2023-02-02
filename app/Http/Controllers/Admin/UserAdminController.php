<?php

namespace Masso\Http\Controllers\Admin;

use Masso\Http\Requests\UserAdminStoreRequest;
use Illuminate\Http\Request;
use Masso\Http\Requests\UserAdminUpdateRequest;
use Masso\User;
use Masso\Role;
use Masso\Log;

class UserAdminController extends AdminController
{


    public function index(Request $request)
    {
        $filter = $request->get('search', false);
        $roles = Role::pluck('id')->toArray();
        $users = User::whereIn('role_id', [1]);

        if( !empty($filter) )
            $users = $users->where(function($query) use ($filter) {
                return $query->where('name', 'LIKE', '%'.$filter.'%')
                        ->orWhere('email', 'LIKE', '%'.$filter.'%');
            });

        $users = $users->paginate(20);
        $title = 'Listado de Administradores';
        return view('admin.general.admin.index', compact('users', 'title') );  
    }

	public function create()
    {
        $roles = Role::pluck('name', 'id');
        $title = 'Crear Nuevo Administrador';
        return view('admin.general.admin.create', compact('roles','title'));
    }


	public function store( UserAdminStoreRequest $request )
    {

    	$data = $request->all();
        $data['password'] = bcrypt($data['password']);
        $data['enabled'] = 1;
        $data['role_id'] = 1;

    	if( User::create( $data ) ):
            Log::create(['area'=>'General', 'module'=>'Admin', 'action'=>'Creó administrador '.$data['rut'], 'user_id'=>\Auth::user()->id]);
    		\Session::flash('success_alert', 'El administrador ha sido creado exitosamente.');
            return \Redirect::route('g.admin.index');
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }



	public function edit($id)
    {
        $user = User::where('id', $id)->first();

        if( empty($user) )
            abort(404);

        $roles = Role::pluck('name', 'id');
        $title = 'Editar Administrador '.$user->name;
        return view('admin.general.admin.edit', compact('user','roles','title'));
    }


	public function update( UserAdminUpdateRequest $request, $id )
    {

    	$user = User::findOrFail($id);
    	$data = $request->only('rut', 'email', 'name');
    	$user->fill( $data );

        $password = $request->get('password', '');

        if( !empty($password) )
            $user->password = bcrypt($password);

    	if( $user->save() ):
            Log::create(['area'=>'General', 'module'=>'Admin', 'action'=>'Editó administrador '.$user->rut, 'user_id'=>\Auth::user()->id]);
    		\Session::flash('success_alert', 'El usuario ha sido actualizado exitosamente.');
            return \Redirect::route('g.admin.index');
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }


    public function destroy($id)
    {

        $user = User::find($id);

        if( !empty($user) ):
            Log::create(['area'=>'General', 'module'=>'Admin', 'action'=>'Eliminó administrador '.$user->rut, 'user_id'=>\Auth::user()->id]);
            $user->email = time().'_'.$user->email;
            $user->rut = time().'_'.$user->rut;
            $user->save();
            $user->delete();
        endif;

		\Session::flash('success_alert', 'El usuario ha sido eliminado exitosamente.');
        return \Redirect::back()->withInput();
    }
}