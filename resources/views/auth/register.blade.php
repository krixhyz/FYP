@extends('layouts.guest')

@section('content')
    <h1 class="text-xl font-semibold mb-4">Create an account</h1>

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 text-red-700 p-3 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium mb-1">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required
                   class="block w-full rounded border-gray-300 focus:border-gray-900 focus:ring-gray-900">
            @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium mb-1">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                   class="block w-full rounded border-gray-300 focus:border-gray-900 focus:ring-gray-900">
            @error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium mb-1">Password</label>
            <input id="password" name="password" type="password" required
                   class="block w-full rounded border-gray-300 focus:border-gray-900 focus:ring-gray-900">
            @error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium mb-1">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required
                   class="block w-full rounded border-gray-300 focus:border-gray-900 focus:ring-gray-900">
        </div>

        @include('partials.location-picker', [
            'pickerId' => 'registerDefaultLocationPicker',
            'label' => 'Default Location',
            'textName' => 'default_location_text',
            'textValue' => old('default_location_text'),
            'cityName' => 'default_city',
            'cityValue' => old('default_city'),
            'latName' => 'default_latitude',
            'latValue' => old('default_latitude'),
            'lngName' => 'default_longitude',
            'lngValue' => old('default_longitude'),
            'placeIdName' => 'default_place_id',
            'placeIdValue' => old('default_place_id'),
            'required' => true,
            'helpText' => 'Set your default location for listings and future delivery details.',
        ])

        <button type="submit"
                class="w-full rounded bg-gray-900 text-white py-2 hover:bg-gray-800">
            Register
        </button>
    </form>

    <p class="mt-4 text-sm text-gray-600">
        Already have an account?
        <a href="{{ route('login') }}" class="text-gray-900 hover:underline">Log in</a>


@endsection
