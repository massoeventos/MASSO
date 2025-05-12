<?php

namespace Masso\Http\Requests;
use Masso\Cities;
use Masso\User;
use Illuminate\Support\Facades\Input;
use Illuminate\Foundation\Http\FormRequest;
use Masso\Country;

class EnrollRequest extends FormRequest
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
        $uri = explode('/', $_SERVER['REQUEST_URI']);
        $event = \Masso\Event::where('slug', $uri[1])->first();

        // Limpiar el RUT que recibirá el controller
        if ($this->has('rut')) {
            $cleanRut = strtoupper(preg_replace('/[^0-9Kk]/', '', $this->input('rut')));
            $this->merge(['rut' => $cleanRut]);
        }

        if ($this->has('invoice_data.rut')) {
            $data = $this->input('invoice_data');
            $data['rut'] = strtoupper(preg_replace('/[^0-9Kk]/', '', $data['rut']));
            $this->merge(['invoice_data' => $data]);
        }

        $rules = [
            'name'  => 'required',
            'lastname'  => 'required',
            'email'     => 'required|email',
            'payment'  => 'required|in:webpay,transfer,free',
            'nationality_country_id' => 'required|exists:countries,id',

            'billing_method' => 'required|in:receipt,invoice',
            'invoice_data' => 'required_if:billing_method,invoice|array',
            'invoice_data.business_name' => 'required_if:billing_method,invoice|string|max:100',
            'invoice_data.rut' => 'required_if:billing_method,invoice|valid_rut',
            'invoice_data.business_activity' => 'required_if:billing_method,invoice|string|max:100',
            'invoice_data.address' => 'required_if:billing_method,invoice|string|max:200',
            'invoice_data.city' => 'required_if:billing_method,invoice|string|max:100',
            'invoice_data.phone' => 'required_if:billing_method,invoice|string|max:20',
            'invoice_data.note' => 'nullable|string|max:400',
        ];

        $chile = Country::where('name', Country::$CHILE_NAME)->firstOrFail();

        if( empty($event) ){
        	$rules['event'] = 'required';
        }
        else{
            // Validar rut o passport según nacionalidad
            if ($this->input('nationality_country_id') == $chile->id) { // es chile
                $rules['rut'] = 'required|valid_rut';       
            } else {
                $rules['passport'] = 'required';              
            }

            if ($event->show_location_fields) {
                $rules['country_id'] = 'required|exists:countries,id';

                if ($this->input('country_id') == $chile->id) { // es chile
                    $rules['city_id'] = 'required|exists:cities,id';
                } else {
                    $rules['custom_city'] = 'required|string|max:100';
                }
            }

        	$tickets = implode(',', $event->tickets()->pluck('id')->toArray());
        	$rules['ticket.*'] = 'required|in:'.$tickets;

        	if( $event->inputs()->count() > 0 ):

        		foreach( $event->inputs as $input ):

        			if( $input->type == 'file' ):
        				$rules[str_replace([' '], ['_'], $input->name)] = 'file';
        				if( $input->required ):
							$rules[str_replace([' '], ['_'], $input->name)] .= '|required';
						endif;
        			else:
        				if( $input->required ):
							$rules[str_replace([' '], ['_'], $input->name)] = 'required';
						endif;
        			endif;

        		endforeach;

        	endif;
        }

        return $rules;
    }

}
