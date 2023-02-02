<?php
namespace Masso\Behaviors;

class UrlBehavior{

    public function getUrlFilterable( $field, $value ){

        $url     = request()->url().'?';
        $queries = request()->query();

        if( isset($queries['page']) )
            unset($queries['page']);

        if( !isset($queries[$field]) )
            $queries[$field] = $value;

        $params  = [];

        foreach( $queries as $key => $query ):

            if( $key == $field )
                $params[] = $key.'='.$value;
            else
                $params[] = $key.'='.$query;

        endforeach;

        return $url.implode('&', $params);
    }

    public function getUrlExcept( $field ){

        $url     = request()->url().'?';
        $queries = request()->query();
        $params  = [];

        if( isset($queries[$field]) )
            unset($queries[$field]);

        if( !empty($queries) )
            foreach( $queries as $key => $query ) 
                $params[] = $key.'='.$query;

        return $url.implode('&', $params);
    }

    
}