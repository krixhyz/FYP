<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'default_location_text' => ['nullable', 'string', 'max:255', 'required_with:default_latitude,default_longitude'],
            'default_city' => ['nullable', 'string', 'max:120'],
            'default_latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:default_location_text,default_longitude'],
            'default_longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:default_location_text,default_latitude'],
            'default_place_id' => ['nullable', 'string', 'max:100'],
        ];
    }
}
