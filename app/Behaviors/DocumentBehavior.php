<?php

namespace Masso\Behaviors;

class DocumentBehavior{


    public static function moveFile( $relative, $file, $name = '' ){

        $filename = $name.'_'.strtolower(str_random(8) . '.' . $file->getClientOriginalExtension());
        $file->move(public_path($relative), $filename);

        return '/'.$relative.$filename;

    }

    
}