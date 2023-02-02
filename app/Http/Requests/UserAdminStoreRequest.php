<?php

namespace Masso\Http\Requests;
use Illuminate\Support\Facades\Input;
use Illuminate\Foundation\Http\FormRequest;

class UserAdminStoreRequest extends FormRequest
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
            'name'      => 'required|min:3',
            'email'     => '',
            'password'  => 'required',
            'rut'       => 'required|min:3|unique:users,rut',
        ];

        return $rules;
    }

}