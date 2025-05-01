<?php

namespace Masso\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    protected $signature = 'test:mail';
    protected $description = 'Enviar un correo de prueba';

    public function handle()
    {
        Mail::raw('Este es un correo de prueba desde Laravel.', function ($message) {
            $message->to(env('DEV_TESTING_MAIL'))
                    ->subject('Correo de prueba');
        });

        $this->info('Correo de prueba enviado.');
    }
}
