<?php

namespace Masso\Behaviors;

class PeopleBehavior{


   public static function generateCode(){
        $letras = "BCDFGHJKLMNPQRST";
        $letras_2 = "TSRQPNMLKJHGFDCB";
        
        $numeros = "123456789";
        
        
        srand((double)microtime()*rand(12345678,987654321));
        $i = 0;
        $frase = '';

        
        while ($i <= 1) {
            $num = mt_rand() % 16;
            $tmp = substr($letras, $num, 1);
            $frase = $frase . $tmp;
            $i++;
           }
        $i=0;
        while ($i <= 2) {
            $num = mt_rand() % 9;
            $tmp = substr($numeros, $num, 1);
            $frase = $frase . $tmp;
            $i++;
           }

        $i=0;
        while ($i <= 1) {
            $num = mt_rand() % 16;
            $tmp = substr($letras_2, $num, 1);
            $frase = $frase . $tmp;
            $i++;
           }

        

        return $frase;
    }


    public static function generatePassword(){

        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 10; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    
    }

    
}