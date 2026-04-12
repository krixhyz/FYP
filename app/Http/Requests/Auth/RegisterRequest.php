<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function messages(): array
    {
        return [
            'phone_number.regex' => 'Phone number must be exactly 10 digits.',
            'phone_number.size' => 'Phone number must be exactly 10 digits.',
        ];
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number' => ['nullable', 'string', 'regex:/^[0-9]{10}$/', 'size:10'],
            'address' => ['nullable', 'string', 'max:1000'],
            'province_id' => ['required', 'exists:provinces,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
