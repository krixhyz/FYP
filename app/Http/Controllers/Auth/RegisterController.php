<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed'],
            'default_location_text' => ['required', 'string', 'max:255'],
            'default_city' => ['nullable', 'string', 'max:120'],
            'default_latitude' => ['required', 'numeric', 'between:-90,90'],
            'default_longitude' => ['required', 'numeric', 'between:-180,180'],
            'default_place_id' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'default_location_text' => $data['default_location_text'],
            'default_city' => $data['default_city'] ?? null,
            'default_latitude' => $data['default_latitude'],
            'default_longitude' => $data['default_longitude'],
            'default_place_id' => $data['default_place_id'] ?? null,
        ]);

        Auth::login($user);

        return redirect('/dashboard');
    }
}
