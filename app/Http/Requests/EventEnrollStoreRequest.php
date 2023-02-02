<?php
namespace Masso\Http\Requests;
use Illuminate\Support\Facades\Input;
use Illuminate\Foundation\Http\FormRequest;

class EventEnrollStoreRequest extends FormRequest
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

        if( isset($input['tickets']))
            foreach( $input['tickets'] as $key => $ticket )
                $input['tickets'][$key]['price'] = str_replace(['$', 'e', ',', '.'],['','','',''],$ticket['price']);
            
        $this->replace($input);
    }


    protected function getValidatorInstance() {
        $this->sanitize();
        return parent::getValidatorInstance();
    }


    public function rules()
    {

        $rules = [
            'name'         			=> 'required',
            'lastname'      		=> 'required',
            'email'     		=> 'required|email',
            'ticket_id'   		=> 'required',
        ];


        if( !empty($this->early_bird) ):
        	$rules['early_bird_init'] = 'required|date|before:date_init';
        	$rules['early_bird_finish'] = 'required|date|after_or_equal:early_bird_init|before:date_init';
        endif;

        return $rules;
    }

}
