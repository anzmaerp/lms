<?php

namespace App\Http\Requests\Auth;

use App\Traits\ApiResponser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class LoginRequest extends FormRequest
{
    use ApiResponser;

    public function failedValidation(Validator $validator)
    {

        throw new HttpResponseException($this->error('Validation errors', $validator->errors()));
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return [
            // 'username' => ['required', 'string', 'regex:/^(\+?\(?\d{1,4}\)?)?[\d\s\-]{7,15}$|^[\w\.\-]+@([\w\-]+\.)+[\w\-]{2,4}$/'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}