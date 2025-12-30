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


        if( !empty($input['tickets']))
            foreach( $input['tickets'] as $key => $ticket ) {
                $input['tickets'][$key]['price'] = str_replace(['$', 'e', ',', '.'], ['', '', '', ''], $ticket['price']);
                $input['tickets'][$key]['is_mandatory'] = $ticket['is_mandatory'] === 'true' ? 1 : 0;
                $input['tickets'][$key]['requires_document'] = (!empty($ticket['requires_document']) && $ticket['requires_document'] === 'true') ? 1 : 0;
            }

        if( !empty($input['inputs']))
            foreach( $input['inputs'] as $key => $eventInput ) {
                if( isset($eventInput['required']) ){
                    $isRequired = ($eventInput['required'] !== 'false' && $eventInput['required'] !== 0 && $eventInput['required'] !== '0');
                    $input['inputs'][$key]['required'] = $isRequired ? 1 : 0;
                }
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
            'show_location_fields'  => 'required|in:0,1',
            'allow_bank_transfer'   => 'required|in:0,1',
            'photo.*'          		=> 'required|image|mimes:jpeg,png,jpg,jpeg,gif',
            'banner_image'          	=> 'nullable|image|mimes:jpeg,png,jpg,jpeg,gif',
            'footer_images.*'        => 'nullable|image|mimes:jpeg,png,jpg,jpeg,gif',

            // Tickets
            'tickets'                    => 'nullable|array',
            'tickets.*.name'             => 'required|string',
            'tickets.*.name_eng'         => 'nullable|string',
            'tickets.*.description'      => 'nullable|string',
            'tickets.*.description_eng'  => 'nullable|string',
            'tickets.*.price'            => 'required|numeric|min:0',
            'tickets.*.stock'            => 'required|integer|min:0',
            'tickets.*.from'             => 'required|date',
            'tickets.*.to'               => 'required|date|after_or_equal:tickets.*.from',
            'tickets.*.is_mandatory'     => 'required|in:0,1',
            'tickets.*.requires_document'=> 'required|in:0,1',

            // Inputs
            'inputs'                     => 'nullable|array',
            'inputs.*.name'              => 'required|string',
            'inputs.*.name_eng'          => 'required|string',
            'inputs.*.type'              => 'required|string|in:text,file',
            'inputs.*.required'          => 'required|in:0,1',
        ];


        if( !empty($this->early_bird) ):
        	$rules['early_bird_init'] = 'required|date|before:date_init';
        	$rules['early_bird_finish'] = 'required|date|after_or_equal:early_bird_init|before:date_init';
        endif;

        return $rules;
    }

}
