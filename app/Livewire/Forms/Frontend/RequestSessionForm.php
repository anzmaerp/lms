<?php

namespace App\Livewire\Forms\Frontend;

use App\Http\Requests\Student\Booking\RequestSessionRequest;
use Livewire\Form;
use App\Traits\PrepareForValidation;

class RequestSessionForm extends Form
{
    use PrepareForValidation;

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public $pdf = null; 
    public string $message = '';
    public string $type = 'private';

    private ?RequestSessionRequest $requestSessionRequest = null;

    public function boot()
    {
        $this->requestSessionRequest = new RequestSessionRequest();
    }

    public function rules()
    {
        return $this->requestSessionRequest->rules();
    }

    public function messages()
    {
        return $this->requestSessionRequest->messages();
    }

    public function validateData()
    {
        \Log::info('Validating form data', ['pdf' => $this->pdf]);
        $this->beforeValidation(['first_name', 'last_name', 'message']);
        $this->validate();
    }

    public function setUserFormData($user)
    {
        $this->first_name = $user?->profile?->first_name ?? '';
        $this->last_name = $user?->profile?->last_name ?? '';
        $this->email = $user?->email ?? '';
    }
}