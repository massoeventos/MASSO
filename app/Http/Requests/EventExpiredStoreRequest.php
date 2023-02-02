<?php
namespace Masso\Http\Requests;
use Illuminate\Support\Facades\Input;
use Illuminate\Foundation\Http\FormRequest;

class EventExpiredStoreRequest extends FormRequest
{

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


    public function rules()
    {

        $rules = [
            'name'          => 'required',
            'location'      => 'required',
            'date_init'     => 'required|date',
            'date_finish'   => 'required|date|after_or_equal:date_init',
            'photo.*'         => 'required|image|mimes:jpeg,png,jpg,jpeg,gif'
        ];

        return $rules;
    }

}