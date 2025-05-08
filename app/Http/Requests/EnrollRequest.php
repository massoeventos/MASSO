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

        exit($_errors);

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

        // Limpiar el RUT antes de aplicar las reglas
        if ($this->has('rut')) {
            $cleanRut = strtoupper(preg_replace('/[^0-9Kk]/', '', $this->input('rut')));
            $this->merge(['rut' => $cleanRut]);
        }

        $rules = [
            'name'  => 'required',
            'lastname'  => 'required',
            'passport' => 'required_without:rut',
            'rut'      => 'required_without:passport|valid_rut',
            'email'     => 'required|email',
            'payment'  => 'required|in:webpay,transfer,free',
        ];

        if( empty($event) ){
        	$rules['event'] = 'required';
        }
        else{
            if ($event->show_location_fields) {
                $rules['country_id'] = 'required|exists:countries,id';

                $countryId = $this->input('country_id');
                $chile = Country::where('name', Country::$CHILE_NAME)->first();

                if ($chile && $countryId == $chile->id) {
                    // Si el país es Chile, se requiere city_id
                    $rules['city_id'] = 'required|exists:cities,id';
                    $rules['custom_city'] = 'nullable|string|max:100';
                } else {
                    // Si NO es Chile, se requiere custom_city
                    $rules['custom_city'] = 'required|string|max:100';
                    $rules['city_id'] = 'nullable';
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
