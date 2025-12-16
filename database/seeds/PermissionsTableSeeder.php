<?php

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        
        
        Masso\Permission::truncate();
        \DB::table('permission_role')->truncate();

		$adminRole   = Masso\Role::where('slug', '=', 'admin')->first();
        $gerenRole   = Masso\Role::where('slug', '=', 'gerencia')->first();
        $ventaRole   = Masso\Role::where('slug', '=', 'ventas')->first();
		$contaRole   = Masso\Role::where('slug', '=', 'contabilidad')->first();
        $invenRole   = Masso\Role::where('slug', '=', 'inventario')->first();
        $codbaRole   = Masso\Role::where('slug', '=', 'codbarra')->first();
        $bodegRole   = Masso\Role::where('slug', '=', 'bodega')->first();
        $pickiRole   = Masso\Role::where('slug', '=', 'pickeador')->first();
        $fizcaRole   = Masso\Role::where('slug', '=', 'fiscalizador')->first();
        $vibodRole   = Masso\Role::where('slug', '=', 'visor-bodega')->first();
        $edbodRole   = Masso\Role::where('slug', '=', 'editor-bodega')->first();

        $permissions = [

        	['name'=>'Dashboard', 'slug'=>'dashboard.index', 'roles' => 'admin'],

            ['name' => 'Eventos - Index', 'slug' => 'events.index', 'roles' => 'admin'],
            ['name' => 'Eventos - Crear', 'slug' => 'events.create', 'roles' => 'admin'],
            ['name' => 'Eventos - Almacenar', 'slug' => 'events.store', 'roles' => 'admin'],
            ['name' => 'Eventos - Editar', 'slug' => 'events.edit', 'roles' => 'admin'],
            ['name' => 'Eventos - Actualizar', 'slug' => 'events.update', 'roles' => 'admin'],
            ['name' => 'Eventos - Eliminar', 'slug' => 'events.destroy', 'roles' => 'admin'],

            //['name' => 'Inscritos - Index', 'slug' => 'enrolls.index', 'roles' => 'admin'],

            ['name' => 'Expirados - Index', 'slug' => 'events.expired', 'roles' => 'admin'],

            ['name' => 'Archivos - Index', 'slug' => 'files.index', 'roles' => 'admin'],
            ['name' => 'Archivos - Crear', 'slug' => 'files.create', 'roles' => 'admin'],
            ['name' => 'Archivos - Almacenar', 'slug' => 'files.store', 'roles' => 'admin'],
            ['name' => 'Archivos - Editar', 'slug' => 'files.edit', 'roles' => 'admin'],
            ['name' => 'Archivos - Actualizar', 'slug' => 'files.update', 'roles' => 'admin'],
            ['name' => 'Archivos - Eliminar', 'slug' => 'files.destroy', 'roles' => 'admin'],

            ['name' => 'Pagos - Index', 'slug' => 'payments.index', 'roles' => 'admin'],
            ['name' => 'Pagos - Crear', 'slug' => 'payments.create', 'roles' => 'admin'],
            ['name' => 'Pagos - Almacenar', 'slug' => 'payments.store', 'roles' => 'admin'],
            ['name' => 'Pagos - Ver', 'slug' => 'payments.show', 'roles' => 'admin'],
            ['name' => 'Pagos - Eliminar', 'slug' => 'payments.destroy', 'roles' => 'admin'],

            ['name' => 'Clientes - Index', 'slug' => 'clients.index', 'roles' => 'admin'],
            ['name' => 'Clientes - Crear', 'slug' => 'clients.create', 'roles' => 'admin'],
            ['name' => 'Clientes - Almacenar', 'slug' => 'clients.store', 'roles' => 'admin'],
            ['name' => 'Clientes - Editar', 'slug' => 'clients.edit', 'roles' => 'admin'],
            ['name' => 'Clientes - Actualizar', 'slug' => 'clients.update', 'roles' => 'admin'],
            ['name' => 'Clientes - Eliminar', 'slug' => 'clients.destroy', 'roles' => 'admin'],

            ['name' => 'Encuestas - Index', 'slug' => 'surveys.index', 'roles' => 'admin'],
            ['name' => 'Encuestas - Ver', 'slug' => 'surveys.edit', 'roles' => 'admin'],
            ['name' => 'Encuestas - Eliminar', 'slug' => 'surveys.destroy', 'roles' => 'admin'],

            ['name' => 'Administrador - Index', 'slug' => 'g.admin.index', 'roles' => 'admin'],
            ['name' => 'Administrador - Crear', 'slug' => 'g.admin.create', 'roles' => 'admin'],
            ['name' => 'Administrador - Almacenar', 'slug' => 'g.admin.store', 'roles' => 'admin'],
            ['name' => 'Administrador - Editar', 'slug' => 'g.admin.edit', 'roles' => 'admin'],
            ['name' => 'Administrador - Actualizar', 'slug' => 'g.admin.update', 'roles' => 'admin'],
            ['name' => 'Administrador - Eliminar', 'slug' => 'g.admin.destroy', 'roles' => 'admin'],

            ['name' => 'Log - Index', 'slug' => 'g.log.index', 'roles' => 'admin'],
        ];

        foreach($permissions as $permission):
            
        	$_permission = Masso\Permission::create([
        		'name' => $permission['name'],
        		'slug' => $permission['slug'],
        		'description' => '',
        	]);

        	$assign = (isset($permission['roles'])) ? explode('|', $permission['roles']) : [];

            if( in_array( 'all', $assign ) ){
                $assign = ['admin', 'contabilidad', 'inventario','codbarra','bodega', 'pickeador', 'fiscalizador','gerencia','ventas'|'editor-bodega'|'visor-bodega'];
            }

            if( !in_array('notadmin', $assign) ){
                $adminRole->permissions()->attach($_permission->id);                
            }
        	
            if( in_array( 'contabilidad', $assign ) ){
                $contaRole->permissions()->attach($_permission->id);    
            }

            if( in_array( 'ventas', $assign ) ){
                $ventaRole->permissions()->attach($_permission->id);    
            }

            if( in_array( 'gerencia', $assign ) ){
                $gerenRole->permissions()->attach($_permission->id);    
            }

        	if( in_array( 'inventario', $assign ) ){
				$invenRole->permissions()->attach($_permission->id);	
        	}

            if( in_array( 'codbarra', $assign ) ){
                $codbaRole->permissions()->attach($_permission->id);    
            }

            if( in_array( 'bodega', $assign ) ){
                $bodegRole->permissions()->attach($_permission->id);    
            }

            if( in_array( 'editor-bodega', $assign ) ){
                $edbodRole->permissions()->attach($_permission->id);    
            }

            if( in_array( 'visor-bodega', $assign ) ){
                $vibodRole->permissions()->attach($_permission->id);    
            }

            if( in_array( 'pickeador', $assign ) ){
                $pickiRole->permissions()->attach($_permission->id);    
            }

            if( in_array( 'fiscalizador', $assign ) ){
                $fizcaRole->permissions()->attach($_permission->id);    
            }

        endforeach;

    }
}
