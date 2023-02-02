<?php
namespace Masso\Behaviors;

class FileBehavior{

	public static function upload( $name, $path, $request ){

        $file = $request->file($name);
        $name = $file->getClientOriginalName();
        $filename = date('Ymdhi').'-'.strtolower($name);

        $upload_success = $file->move($path, $filename);
        return '/'.$path.$filename;

	
	}
}

