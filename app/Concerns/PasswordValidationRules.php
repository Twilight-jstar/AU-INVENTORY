<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, Rule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        return [
            'required',
            'string',
            Password::min(8)        // Minimum of 8 characters
                ->letters()         // Dapat may letters
                ->mixedCase()       // Dapat may uppercase (A-Z) at lowercase (a-z)
                ->numbers()         // Dapat may numero (0-9)
                ->symbols(),        // Dapat may special character (!, @, #, $, etc.)
            'max:32',               // Maximum of 32 characters para hindi masyadong mahaba
            'confirmed'             // Hahanapin yung `password_confirmation` field sa form
        ];
    }

    /**
     * Get the validation rules used to validate the current password.
     *
     * @return array<int, Rule|array<mixed>|string>
     */
    protected function currentPasswordRules(): array
    {
        return ['required', 'string', 'current_password'];
    }
}