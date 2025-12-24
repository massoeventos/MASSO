<?php
namespace Masso\Behaviors;

class FileBehavior{

        public static function upload($fileOrName, $path, $request = null){

                // Accept either an UploadedFile instance or a field name with a Request
                $file = ($fileOrName instanceof \Illuminate\Http\UploadedFile)
                        ? $fileOrName
                        : ($request ? $request->file($fileOrName) : null);

                if (!$file) {
                        return null;
                }

                $name = $file->getClientOriginalName();
                $filename = date('Ymdhi').'-'.strtolower($name);

                $file->move($path, $filename);
                return '/'.$path.$filename;

        }
}

