<?php

namespace Modules\KuponDeal\Requests;


use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'form.code' => ['required', 'max:255', 'regex:/^\S*$/'],
            'form.discount_type' => 'required|string|max:255',
            'form.discount_value' => [
                'required',
                'numeric',
                'min:0',
            ],
            'form.expiry_date' => 'required|date',
            'form.couponable_type' => 'required|string|max:255',
            'form.couponable_id' => 'required|array',
            'form.color' => 'required|string|max:50',
            'form.description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages()
    {
        return [
            'form.code.required' => 'The code field is required.',
            'form.code.unique' => 'This code has already been taken.',
            'form.code.string' => 'The code field must be a string.',
            'form.code.max' => 'The code field must be at most 50 characters.',
            'form.code.min' => 'The code field must be at least 3 characters.',
            'form.code.regex' => 'The coupon code cannot contain spaces.',
            'form.discount_type.required' => 'The discount type field is required.',
            'form.discount_value.required' => 'The discount value field is required.',
            'form.discount_value.numeric' => 'The discount value field must be a number.',
            'form.discount_value.min' => 'The discount value field must be at least 0.',
            'form.discount_value.max' => 'The discount value field must be at most 100.',
            'form.discount_value.when' => 'The discount value field must be at most 100 when discount type is percentage.',
            'form.expiry_date.required' => 'The expiry date field is required.',
            'form.expiry_date.date' => 'The expiry date field must be a date.',
            'form.couponable_type.required' => 'The couponable type field is required.',
            'form.couponable_id.required' => 'The couponable id field is required.',
            'form.color.required' => 'The color field is required.',
        ];
    }
}
