<?php

namespace Modules\CourseBundles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BundleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
       
        return [
            'title'                 => 'required|string|max:255|min:3',
            'short_description'     => 'required|string|max:255|min:3',
            'selected_courses'      => 'required|array|min:1',
            'image'                 => 'required',
            'price'                 => isPaidSystem() ? 'required|numeric' : 'nullable',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }
}
