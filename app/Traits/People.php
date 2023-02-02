<?php

namespace Masso\Traits;
use Masso\Behaviors\PeopleBehavior;
use Masso\Behaviors\DocumentBehavior;

trait People{

    private static $defaultPhoto = '/resources/default-profile.jpg';
    private $productCredits = 'none';

    public function productTransactions()
    {
        return $this->hasMany('Masso\ProductTransaction');
    }


    public function getName(){
        return $this->name;
    }

    public function getFullName(){
        return $this->name.' '.$this->middle_name.' '.$this->last_name;
    }

    public function getProfilePhoto(){
        if( $this->photo != '' )
            return $this->photo;

        return self::$defaultPhoto;

    }

    public function getProductsCredits(){

        if( $this->productCredits == 'none' )
            $this->productCredits = $this->productTransactions()->where('status', 'available')->count();

        return $this->productCredits;
    }

    public function getEmail(){
        return $this->email;
    }

    public function getStatus(){
        return ($this->enabled == 0) ? 'Inactivo' : 'Activo';
    }

    public static function generatePassword(){
        return PeopleBehavior::generatePassword();
    }



}