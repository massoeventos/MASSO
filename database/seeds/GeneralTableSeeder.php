<?php

use Illuminate\Database\Seeder;

class GeneralTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $member = Masso\EventExpired::create([
                'name'          => 'IV Simposio Informática en Salud',
        		'location'	  	=> 'Clínica Alemana de Santiago.',
        		'date_init'		=> '2018-09-27',
                'date_finish'   => '2018-09-28',
                'photo'			=> '',
        	]);

        $member = Masso\EventExpired::create([
                'name'          => 'Simposio Inmunodeficiencias Primarias y Disregulación Inmune',
        		'location'	  	=> 'Aula Magna. Clínica Alemana de Santiago',
        		'date_init'		=> '2018-11-05',
                'date_finish'   => '2018-11-10',
                'photo'			=> '/images/events/afiche-inmuno.png',
        	]);
        


    }
}
