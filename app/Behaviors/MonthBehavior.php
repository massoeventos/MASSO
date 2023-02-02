<?php

namespace Masso\Behaviors;

class MonthBehavior{

	public static $months = [
    		'Jan' => 'Enero',
    		'Feb' => 'Febrero',
    		'Mar' => 'Marzo',
    		'Apr' => 'Abril',
    		'May' => 'Mayo',
    		'Jun' => 'Junio',
    		'Jul' => 'Julio',
    		'Aug' => 'Agosto',
    		'Sep' => 'Septiembre',
    		'Oct' => 'Octubre',
    		'Nov' => 'Noviembre',
    		'Dec' => 'Diciembre',
    	];	

    public static function translate( $month ){

    	

    	return isset(self::$months[$month]) ? self::$months[$month] : $month;

    }

    public static function getTimeFromString( $string ){

    	$explode = explode('-', $string);
    	$month 	 = array_search($explode[0], self::$months);

    	$date = date('Y', strtotime($explode[1].'-'.$month.'-01'));

    	if( $date == '1969' )
    		return time();

    	$date = date('Y-m-d', strtotime($explode[1].'-'.$month.'-01'));
    	return strtotime($date);
    }

    
}