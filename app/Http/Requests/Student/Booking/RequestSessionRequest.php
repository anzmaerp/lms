<?php

namespace App\Http\Requests\Student\Booking;

use Illuminate\Foundation\Http\FormRequest;

class RequestSessionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email',
            'message' => 'required|string|max:16777215',
            'type' => 'required|string|in:private,group',
            'pdf' => 'nullable|file|mimes:pdf|max:10240', // 10MB max, PDF only
        ];
    }

    public function messages()
    {
        return [
            'required' => __('validation.required_field'),
            'email' => __('validation.email'),
            'pdf.mimes' => __('validation.mimes', ['attribute' => 'pdf', 'values' => 'pdf']),
            'pdf.max' => __('validation.max.file', ['attribute' => 'pdf', 'max' => '10MB']),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => sanitizeTextField($this->first_name),
            'last_name' => sanitizeTextField($this->last_name),
            'message' => sanitizeTextField($this->message),
            // Exclude 'pdf' from sanitization as it's a file
        ]);
    }
}