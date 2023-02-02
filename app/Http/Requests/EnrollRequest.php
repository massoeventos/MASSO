<?php

namespace Masso\Http\Requests;
use Masso\Cities;
use Masso\User;
use Illuminate\Support\Facades\Input;
use Illuminate\Foundation\Http\FormRequest;

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
        $rules = [
            'name'  => 'required',
            'lastname'  => 'required',
            'passport'       => 'required',
            'email'     => 'required|email',
            'payment'  => 'required|in:webpay,transfer,free'
        ];

        $uri = explode('/', $_SERVER['REQUEST_URI']);
        $event = \Masso\Event::where('slug', $uri[1])->first();

        if( empty($event) ):
        	$rules['event'] = 'required';
        else:

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

        endif;

        return $rules;
    }

}
