<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */
    'uploaded'             => 'Ocurrió un error al procesar los archivos, contacte con el administrador del sistema',
    'accepted'             => 'El campo :attribute debe ser aceptado.',
    'active_url'           => 'El campo :attribute no es una URL válida.',
    'after'                => 'El campo :attribute debe ser una fecha posterior a :date.',
    'alpha'                => 'El campo :attribute solo puede contener letras.',
    'alpha_dash'           => 'El campo :attribute solo puede contener letras, números y guiones.',
    'alpha_num'            => 'El campo :attribute solo puede contener letras y números.',
    'array'                => 'El campo :attribute debe ser un arreglo.',
    'before'               => 'El campo :attribute debe ser una fecha anterior a :date.',
    'between'              => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'file'    => 'El campo :attribute debe estar entre :min y :max kilobytes.',
        'string'  => 'El campo :attribute debe tener entre :min y :max caracteres.',
        'array'   => 'El campo :attribute debe tener entre :min y :max elementos.',
    ],
    'boolean'              => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed'            => 'La confirmación de :attribute no coincide.',
    'date'                 => 'El campo :attribute no es una fecha válida.',
    'date_format'          => 'El campo :attribute no coincide con el formato :format.',
    'different'            => 'Los campos :attribute y :other deben ser diferentes.',
    'digits'               => 'El campo :attribute debe tener :digits dígitos.',
    'digits_between'       => 'El campo :attribute debe tener entre :min y :max dígitos.',
    'email'                => 'El campo :attribute debe ser un correo electrónico válido.',
    'exists'               => 'El :attribute seleccionado no es válido.',
    'filled'               => 'El campo :attribute es obligatorio.',
    'image'                => 'El campo :attribute debe ser una imagen.',
    'in'                   => 'El :attribute seleccionado no es válido.',
    'integer'              => 'El campo :attribute debe ser un número entero.',
    'ip'                   => 'El campo :attribute debe ser una dirección IP válida.',
    'json'                 => 'El campo :attribute debe ser una cadena JSON válida.',
    'max'                  => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'file'    => 'El campo :attribute no debe ser mayor que :max kilobytes.',
        'string'  => 'El campo :attribute no debe ser mayor que :max caracteres.',
        'array'   => 'El campo :attribute no debe tener más de :max elementos.',
    ],
    'mimes'                => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'min'                  => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'file'    => 'El campo :attribute debe ser al menos de :min kilobytes.',
        'string'  => 'El nombre debe contener al menos :min caracteres.',
        'array'   => 'El campo :attribute debe tener al menos :min elementos.',
    ],
    'not_in'               => 'El :attribute seleccionado no es válido.',
    'numeric'              => 'El campo :attribute debe ser un número.',
    'regex'                => 'El formato de :attribute no es válido.',
    'required'             => 'El campo :attribute es obligatorio.',
    'required_if'          => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_unless'      => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
    'required_with'        => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_with_all'    => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_without'     => 'El campo :attribute es obligatorio cuando :values no está presente.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de :values está presente.',
    'same'                 => 'Los campos :attribute y :other deben coincidir.',
    'size'                 => [
        'numeric' => 'El campo :attribute debe ser :size.',
        'file'    => 'El campo :attribute debe ser de :size kilobytes.',
        'string'  => 'El campo :attribute debe tener :size caracteres.',
        'array'   => 'El campo :attribute debe contener :size elementos.',
    ],
    'string'               => 'El campo :attribute debe ser una cadena de texto.',
    'timezone'             => 'El campo :attribute debe ser una zona válida.',
    'unique'               => 'El campo :attribute ya ha sido registrado.',
    'url'                  => 'El formato de :attribute no es válido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'name' => [
            'alpha_num' => 'El nombre sólo puede contener letras o números.', 
            'required'  => 'Debe ingresar un nombre.',
            'min'       => 'El nombre debe contener all menos 3 carácteres',
        ],
        'rut' => [
            'required' => 'El rut ingresado no es válido.', 
            'unique'  => 'El rut ingresado ya se encuentra asociado a un usuario.',
            'min'       => 'El nombre debe contener all menos 3 carácteres',
        ],
        'lastname' => [
            'alpha_num' => 'El apellido sólo puede contener letras o números.', 
            'required'  => 'Debe ingresar un apellido.',
            'min'       => 'El apellido debe contener all menos 3 carácteres',
        ],
        'email' => [
            'alpha_num' => 'El email sólo puede contener letras o números.', 
            'required' => 'Debe ingresar un email.',
            'unique'    => 'El email ingresado esta asociado a otro usuario.',
            'email'     => 'El correo ingresado no es válido.'
        ],
        'email_products' => [
            'required'     => 'El correo ingresado para las notificaciones de compras de productos no es válido.',
            'email'     => 'El correo ingresado para las notificaciones de compras de productos no es válido.'
        ],
        'description' => [
            'min' => 'Debe ingresar una descripción más extensa.', 
            'required' => 'Debe ingresar una descripción.',
        ],
        'captcha' => [
            'required' => 'Debe confirmar que no es un robot.',
        ],
        'phone' => [
            'min' => 'Debe ingresar un número de teléfono válido.', 
            'required' => 'Debe ingresar un número de teléfono válido.',
        ],
        'comment' => [
            'min' => 'Debe especificar su comentario y/o consulta claramente.',
            'required' => 'Debe especificar su comentario y/o consulta.',
        ],
        'comuna' => [
            'min'       => 'La comuna ingresada no es válida',
            'required' => 'La comuna ingresada no es válida.',
        ],
        
        'password' => [
            'required' => 'Debe ingresar una contraseña de acceso',        
            'confirmed' => 'Las contraseñas ingresadas no coinciden',
        ],
        'address' => [
            'min' => 'Debe ingresar una dirección',        
            'required' => 'Debe ingresar una dirección',
        ],
        'date_finish' => [
            'after_or_equal' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ],
        'photo' => [
            'required'  => 'Debe ingresar la imagen',
            'image'     => 'El archivo debe ser una imagen', 
        ],


    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
    ],

];
