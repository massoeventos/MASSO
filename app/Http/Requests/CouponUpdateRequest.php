<?php

namespace Masso\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponUpdateRequest extends FormRequest
{
    public function response(array $errors)
    {
        $_errors = '';

        if (sizeof($errors) > 0) {
            $_errors = "<br>";
            foreach ($errors as $error) {
                $_errors .= "<li>" . $error[0] . "</li>";
            }
        }

        \Session::flash('error_alert', 'Se encontraron algunos errores al validar la solicitud.' . $_errors);
        return \Redirect::back()
            ->withInput()
            ->withErrors($errors);
    }

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        $rules = [];

        if (!empty($this->coupons)) {
            foreach ($this->coupons as $index => $coupon) {
                $rules["coupons.$index.code"] = 'required|string|max:255';
                $rules["coupons.$index.discount_percentage"] = 'required|integer|min:1|max:100';
                $rules["coupons.$index.usage_limit"] = 'nullable|integer|min:1';
                $rules["coupons.$index.starts_at"] = 'nullable|date';
                $rules["coupons.$index.ends_at"] = 'nullable|date|after_or_equal:coupons.' . $index . '.starts_at';
                $rules["coupons.$index.coupon_tickets"] = 'nullable|array';
                $rules["coupons.$index.coupon_tickets.*"] = 'integer|exists:events_tickets,id';
            }
        }

        return $rules;
    }
}
