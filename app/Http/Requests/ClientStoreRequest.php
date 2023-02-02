<?php

namespace Masso\Http\Requests;
use Masso\Cities;
use Masso\User;
use Illuminate\Support\Facades\Input;
use Illuminate\Foundation\Http\FormRequest;

class ClientStoreRequest extends FormRequest
{


    /**
     * Overrides response from the FormRequest
     * to not redirect for our API development
     * @param array $errors
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function response(array $errors)
    {

        $_errors = '';
        
        if( sizeof($errors) > 0){

            $_errors = "<br>";
            foreach($errors as $error){

                $_errors .= "<li>".$error[0]."</li>";
            }
        }

        \Session::flash('error_alert', 'Se encontraron algunos errores al validar la solicitud.'.$_errors);
        return \Redirect::back()
            ->withInput()
            ->withErrors($errors);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


    public function sanitize(){
        $input = $this->all();

        if( !empty($input['comuna_id']) ):
            $comuna = Cities::where('id', $input['comuna_id'])->first();
            $input['provincia_id'] = $comuna->provincia_id;
        endif;

        if( isset($input['foreign']) && $input['foreign'] == 0 ):

            $validate = User::valida_rut($input['rut']);

            if( $validate ):
                $input['validate_rut'] = true;
            endif;

        elseif( isset($input['foreign']) && $input['foreign'] == 1 ):

            $input['validate_rut'] = true; 
            $input['foreign_rut']  = $input['rut'];
            $input['rut']          = User::generateRUT();

        endif;
        

        $this->replace($input);
    }

    protected function getValidatorInstance() {
        $this->sanitize();
        return parent::getValidatorInstance();
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $rules = [
            'name'  => 'required|min:3',
            'rut'       => 'required|min:7|unique:clients,rut',
            #'validate_rut' => 'required',
            'email'     => 'required|email',
        ];

        return $rules;
    }

}
