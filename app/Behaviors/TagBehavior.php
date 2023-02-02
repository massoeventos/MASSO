<?php
namespace Masso\Behaviors;

class TagBehavior{

    public static function generalTags( $category = '' )
    {
        $array = array();

        if( strtolower($category) != 'componentes' )
        {
            $merge = self::tagsByAno();
            $array = array_merge($array, $merge);
        }

        $merge = self::tagsByAro();
        $array = array_merge($array, $merge);

        $merge = self::tagsByColor();
        $array = array_merge($array, $merge);


        $merge = self::tagsByMaterial();
        $array = array_merge($array, $merge);

        $merge = self::tagsByTalla();
        $array = array_merge($array, $merge);
        
        return $array;
    }

    public static function isValidQuery( $type, $value ){
        return (isset(self::generalTags()[$type][$value])) ? self::generalTags()[$type][$value] : false;
    }

    public static function getStoreQuery( $type, $value, $stores){
        if( $value == 'internet' )
            return 'stock > 0';

        if( $value == 'la-serena' )
            return 'stock_laserena > 0';

        if( $value == 'vitacura' )
            return 'stock_vitacura > 0';

        if( $value == 'valdivia' )
            return 'stock_valdivia > 0';
    }

    public static function getRawQuery( $type, $value ){

        $query = self::isValidQuery( $type, $value );

        if( empty($query) )
            abort(500);

        return $query['sentence']." '".$query['search']."'";
    }


    public static function tagsByMaterial()
    {
        return array(
                'materials' => array(
                    'm-acrilico'    => array('search'=>'%acril%'  , 'sentence'=>'LOWER( name ) LIKE', 'name'=>'Acrilico'),
                    'm-acero'       => array('search'=>'%acero%'  , 'sentence'=>'LOWER( name ) LIKE', 'name'=>'Acero'),   
                    'm-alambre'     => array('search'=>'%alamb%'  , 'sentence'=>'LOWER( name ) LIKE', 'name'=>'Alambre'),
                    'm-aluminio'    => array('search'=>'%alum%'   , 'sentence'=>'LOWER( name ) LIKE', 'name'=>'Aluminio'),
                    'm-carbon'      => array('search'=>'%carbon%' , 'sentence'=>'LOWER( name ) LIKE', 'name'=>'Carbon'),
                )
            );
    }

    public static function tagsByTalla()
    {

        return array(
                'sizes' => array(
                    'tb-xxs'=>  array('search'=>'%/XXS/%' , 'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'XXS', 'categoria1'=>'Bicicletas'),
                    'tb-xs' =>  array('search'=>'%/XS/%'  , 'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'XS', 'categoria1'=>'Bicicletas'),
                    'tb-s'  =>  array('search'=>'%/S/%'   , 'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'S', 'categoria1'=>'Bicicletas'),
                    'tb-m'  =>  array('search'=>'%/M/%'   , 'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'M', 'categoria1'=>'Bicicletas'),
                    'tb-l'  =>  array('search'=>'%/L/%'   , 'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'L', 'categoria1'=>'Bicicletas'),
                    'tb-xl' =>  array('search'=>'%/XL/%'  , 'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'XL', 'categoria1'=>'Bicicletas'),
                    'tb-xxl'=>  array('search'=>'%/XXL/%' , 'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'XXL', 'categoria1'=>'Bicicletas'),
                ),
            );

    }


    public static function tagsByAno()
    {
        return array(
                'years' => array(
                    '2020'  =>  array('search'=>'%2020%' , 'sentence'=>'name LIKE', 'name'=>'Año 2020'),
                    '2019'  =>  array('search'=>'%2019%' , 'sentence'=>'name LIKE', 'name'=>'Año 2019'),
                    '2018'  =>  array('search'=>'%2018%' , 'sentence'=>'name LIKE', 'name'=>'Año 2018'),
                    '2017'  =>  array('search'=>'%2017%' , 'sentence'=>'name LIKE', 'name'=>'Año 2017'),
                    '2016'  =>  array('search'=>'%2016%' , 'sentence'=>'name LIKE', 'name'=>'Año 2016'),
                    '2015'  =>  array('search'=>'%2015%' , 'sentence'=>'name LIKE', 'name'=>'Año 2015'),
                    '2014'  =>  array('search'=>'%2014%' , 'sentence'=>'name LIKE', 'name'=>'Año 2014'),
                    '2013'  =>  array('search'=>'%2013%' , 'sentence'=>'name LIKE', 'name'=>'Año 2013'),
                ),
            );

    }

    public static function tagsByAro()
    {
        return array(
                'aros' => array(
                    'ar-26'  =>  array('search'=>'%26%'   , 'sentence'=>'CONCAT(REPLACE(name, " ", "/"),"","/") LIKE', 'name'=>'26'),
                    'ar-27'  =>  array('search'=>'%27.5%' , 'sentence'=>'CONCAT(REPLACE( REPLACE(name, "27,5", "27.5"), " ", "/"),"","/") LIKE', 'name'=>'27.5'),
                    'ar-29'  =>  array('search'=>'%29%'   , 'sentence'=>'CONCAT(REPLACE( REPLACE(name, "29er", "29 er"), " ", "/"),"","/") LIKE', 'name'=>'29er'),
                    'ar-700c'=>  array('search'=>'%700%'  , 'sentence'=>'CONCAT(REPLACE(name, " ", "/"),"","/") LIKE', 'name'=>'700c'),
                ),
            );

    }

    public static function tagsByColor()
    {
        return array(
                'colors' => array(
                    'col-azul'      =>  array('search'=>'%azul%'      ,'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'Azul'),
                    'col-amarillo'  =>  array('search'=>'%amari%'     ,'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'Amarillo'),
                    'col-blanco'    =>  array('search'=>'%blanc%'     ,'sentence'=>'CONCAT(REPLACE(REPLACE(LOWER(name),"white", "blanc"), " ", "/"),"","/") LIKE', 'name'=>'Blanco'),
                    'col-celeste'   =>  array('search'=>'%celes/%'    ,'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'col-celestee'),
                    'col-cromado'   =>  array('search'=>'%crom%'      ,'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'Cromado'),
                    'col-gris'      =>  array('search'=>'%gris%'      ,'sentence'=>'CONCAT(REPLACE(REPLACE(LOWER(name),"gray", "gris"), " ", "/"),"","/") LIKE', 'name'=>'Gris'),
                    'col-negro'     =>  array('search'=>'%negr%'      ,'sentence'=>'CONCAT(REPLACE(REPLACE(LOWER(name),"black", "negro"), " ", "/"),"","/") LIKE', 'name'=>'Negro'),
                    'col-naranjo'   =>  array('search'=>'%naranj%'    ,'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'Naranjo'),
                    'col-lila'      =>  array('search'=>'%purpur%'    ,'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'Purpura'),
                    'col-plata'     =>  array('search'=>'%plat%'      ,'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'Plateado'),
                    'col-rojo'      =>  array('search'=>'%roj%'       ,'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'Rojo'),
                    'col-rosa'      =>  array('search'=>'%rosad%'     ,'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'Rosado'),
                    'col-verde'     =>  array('search'=>'%verd%'      ,'sentence'=>'CONCAT(LOWER( REPLACE(name, " ", "/")),"","/") LIKE', 'name'=>'Verde'),
                ),
            );

    }   

    
}