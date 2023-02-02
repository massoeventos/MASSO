<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Masso\Role::truncate();
        Masso\Role::create([
        		'name' => 'Administrador',
        		'slug' => 'admin',
        		'description' => 'Administrador',
        	]);

        Masso\Role::create([
                'name' => 'Contabilidad',
                'slug' => 'contabilidad',
                'description' => 'Encargados de contabilidad',
            ]);

        Masso\Role::create([
        		'name' => 'Inventario',
        		'slug' => 'inventario',
        		'description' => 'Encargados de inventarios',
        	]);

        Masso\Role::create([
                'name' => 'Códigos de Barra',
                'slug' => 'codbarra',
                'description' => 'Encargados de códigos de barra',
            ]);

        Masso\Role::create([
                'name' => 'Administrador Bodega',
                'slug' => 'bodega',
                'description' => 'Administrador de Bodega',
            ]);

        Masso\Role::create([
                'name' => 'Pickeador',
                'slug' => 'pickeador',
                'description' => 'Pickeador',
            ]);

        Masso\Role::create([
                'name' => 'Fiscalizador',
                'slug' => 'fiscalizador',
                'description' => 'Fizcalizador',
            ]);

        Masso\Role::create([
                'name' => 'Gerencia',
                'slug' => 'gerencia',
                'description' => 'Gerencia',
            ]);

        Masso\Role::create([
                'name' => 'Ventas',
                'slug' => 'ventas',
                'description' => 'Ventas',
            ]);
        

        Masso\Role::create([
                'name' => 'Editor Productos',
                'slug' => 'editor-bodega',
                'description' => 'Encargados de Edición de Productos',
            ]);

        Masso\Role::create([
                'name' => 'Bodeguero',
                'slug' => 'visor-bodega',
                'description' => 'Bodeguero',
            ]);
        
    }
}
