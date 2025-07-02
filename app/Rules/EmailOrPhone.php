<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class EmailOrPhone implements Rule
{
    public function passes($attribute, $value)
    {
        // Check if it's a valid email
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        // Check if it's a valid phone number
        return preg_match('/^(0|\+?[1-9])\d{7,14}$/', $value);
    }

    public function message()
    {
        return 'The :attribute must be a valid email or a phone number.';
    }
}