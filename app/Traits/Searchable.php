<?php

namespace Masso\Traits;

trait Searchable{

    public static function listAllByLang( $array ){

        return self::pluck($array[0], $array[1])->toArray();

    }

}