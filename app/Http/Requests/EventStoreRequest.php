<?php
namespace Masso\Http\Requests;
use Illuminate\Support\Facades\Input;
use Illuminate\Foundation\Http\FormRequest;

class EventStoreRequest extends FormRequest
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
            foreach( $input['tickets'] as $key => $ticket ) {
                $input['tickets'][$key]['price'] = str_replace(['$', 'e', ',', '.'], ['', '', '', ''], $ticket['price']);
                $input['tickets'][$key]['is_mandatory'] = $ticket['is_mandatory'] === 'true' ? 1 : 0;
            }
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
            'location'      		=> 'required',
            'date_init'     		=> 'required|date',
            'date_finish'   		=> 'required|date|after_or_equal:date_init',
            'status'				=> 'required|in:0,1',
            'photo.*'         		=> 'required|image|mimes:jpeg,png,jpg,jpeg,gif'
        ];


        if( !empty($this->early_bird) ):
        	$rules['early_bird_init'] = 'required|date|before:date_init';
        	$rules['early_bird_finish'] = 'required|date|after_or_equal:early_bird_init|before:date_init';
        endif;

        return $rules;
    }

}
