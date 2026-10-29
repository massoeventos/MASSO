<?php
namespace Masso\Http\Requests;
use Illuminate\Support\Facades\Input;
use Illuminate\Foundation\Http\FormRequest;
use Masso\Http\Requests\Concerns\EventTicketsInput;

class EventUpdateRequest extends FormRequest
{
    use EventTicketsInput;

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

        $input = $this->normalizeTicketsInput($input);

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
            'name_eng'     			=> 'nullable|string|max:255',
            'location'      		=> 'required',
            'date_init'     		=> 'required|date',
            'date_finish'   		=> 'required|date|after_or_equal:date_init',
            'status'				=> 'required|in:0,1',
            'show_location_fields'  => 'required|in:0,1',
            'allow_bank_transfer'   => 'required|in:0,1',
            'rules_consider_previous_purchases' => 'nullable|in:0,1',
            'photo.*'          		=> 'nullable|image|mimes:jpeg,png,jpg,jpeg,gif|max:4096',
            'banner_image'          	=> 'nullable|image|mimes:jpeg,png,jpg,jpeg,gif|max:4096',
            'footer_images.*'        => 'nullable|image|mimes:jpeg,png,jpg,jpeg,gif|max:4096',

            // Categorías y entradas: Concerns\EventTicketsInput
            ...$this->ticketRules(),

            // Inputs
            'inputs'                     => 'nullable|array',
            'inputs.*.name'              => 'required|string',
            'inputs.*.name_eng'          => 'required|string',
            'inputs.*.type'              => 'required|string|in:text,file',
            'inputs.*.required'          => 'required|in:0,1'
        ];


        if( !empty($this->early_bird) ):
        	$rules['early_bird_init'] = 'required|date|before:date_init';
        	$rules['early_bird_finish'] = 'required|date|after_or_equal:early_bird_init|before:date_init';
        endif;

        return $rules;
    }

    public function attributes()
    {
        return $this->ticketAttributes();
    }

    /**
     * El cupo de una entrada existente no puede quedar bajo lo ya vendido
     * (dejaría disponibles negativos).
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $tickets = $this->input('tickets', []);
            if (empty($tickets)) return;

            $existing = \Masso\EventTicket::where('event_id', $this->route('id'))
                ->whereIn('id', array_keys($tickets))
                ->withCount(\Masso\EventTicket::soldCountQuery())
                ->get();

            foreach ($existing as $ticket) {
                $capacity = $tickets[$ticket->id]['capacity'] ?? null;
                if (is_numeric($capacity) && (int) $capacity < $ticket->soldCount()) {
                    $validator->errors()->add(
                        "tickets.{$ticket->id}.capacity",
                        "El cupo de «{$ticket->name}» no puede ser menor que lo ya vendido ({$ticket->soldCount()})."
                    );
                }
            }
        });
    }
}
