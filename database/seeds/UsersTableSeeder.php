<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Masso\User::truncate();


        $member = Masso\User::create([
                'rut'           => 'paola-masso',
        		'email'	  		=> 'paola@massoeventos.cl',
        		'name'			=> 'Paola Masso',
                'role_id'       => '1',
        		'password'		=> bcrypt('masso-plataforma2019')
        	]);

    }
}
