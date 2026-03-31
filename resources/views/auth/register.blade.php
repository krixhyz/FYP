@extends('layouts.guest')

@section('content')
    <div>
        <p class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] mb-2">Create Account</p>
        <h1 class="font-space font-bold text-4xl text-[#1a1c1c] mb-1">Join Nepal Reuse Market</h1>
        <p class="font-manrope text-sm text-[#444746] mb-6">Set up your account to buy, rent, swap, and manage your activity.</p>

        @if ($errors->any())
            <div class="bg-[#ba1a1a] text-white p-4 mb-6 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-6" x-data="registerLocationForm()">
        @csrf

        <div>
            <label for="name" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-1.5">Full Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required
                   class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm focus:border-[#006a38] focus:outline-none focus:ring-0 w-full">
            @error('name')<p class="font-manrope text-sm text-[#ba1a1a] mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-1.5">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                   class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm focus:border-[#006a38] focus:outline-none focus:ring-0 w-full">
            @error('email')<p class="font-manrope text-sm text-[#ba1a1a] mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="province_id" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-1.5">Province</label>
                <select id="province_id"
                        name="province_id"
                        required
                        x-model="selectedProvince"
                        x-on:change="onProvinceChange()"
                        class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm focus:border-[#006a38] focus:outline-none focus:ring-0 w-full">
                    <option value="">Select province</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province->id }}">{{ $province->name }}</option>
                    @endforeach
                </select>
                @error('province_id')<p class="font-manrope text-sm text-[#ba1a1a] mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="city_id" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746]">City</label>
                    <span x-show="loadingCities" class="font-space text-[10px] font-bold uppercase tracking-widest text-[#006a38]">Loading...</span>
                </div>
                <select id="city_id"
                        name="city_id"
                        required
                        x-model="selectedCity"
                        x-bind:disabled="!selectedProvince || loadingCities"
                        class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm focus:border-[#006a38] focus:outline-none focus:ring-0 w-full disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="" x-text="selectedProvince ? 'Select city' : 'Choose province first'"></option>
                    <template x-for="city in cities" :key="city.id">
                        <option :value="city.id" x-text="city.name"></option>
                    </template>
                </select>
                @error('city_id')<p class="font-manrope text-sm text-[#ba1a1a] mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="password" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-1.5">Password</label>
            <input id="password" name="password" type="password" required
                   class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm focus:border-[#006a38] focus:outline-none focus:ring-0 w-full">
            @error('password')<p class="font-manrope text-sm text-[#ba1a1a] mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="font-space text-[11px] font-bold uppercase tracking-widest text-[#444746] block mb-1.5">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required
                   class="bg-[#f3f3f3] border-0 border-b-2 border-gray-400 px-3 py-2.5 font-manrope text-sm focus:border-[#006a38] focus:outline-none focus:ring-0 w-full">
        </div>

        <button type="submit" class="w-full bg-gradient-to-br from-[#006a38] to-[#09864a] text-white py-3 font-space font-bold text-sm uppercase tracking-wider hover:brightness-110 active:brightness-95 transition-all">
            Create Account
        </button>

        <p class="text-center font-manrope text-sm text-[#444746]">
            Already registered?
            <a href="{{ route('login') }}" class="font-space font-bold uppercase text-[#006a38] hover:text-[#004a29] tracking-wider">Sign in here</a>
        </p>
    </form>

    <script>
        function registerLocationForm() {
            return {
                selectedProvince: '{{ old('province_id') }}',
                selectedCity: '{{ old('city_id') }}',
                cities: [],
                loadingCities: false,

                init() {
                    if (this.selectedProvince) {
                        this.fetchCities(this.selectedProvince, this.selectedCity);
                    }
                },

                onProvinceChange() {
                    this.selectedCity = '';
                    this.cities = [];

                    if (!this.selectedProvince) {
                        return;
                    }

                    this.fetchCities(this.selectedProvince);
                },

                async fetchCities(provinceId, oldCity = '') {
                    this.loadingCities = true;

                    try {
                        const response = await fetch(`/api/cities/${provinceId}`);
                        if (!response.ok) {
                            throw new Error('Failed to load cities');
                        }

                        const payload = await response.json();
                        this.cities = Array.isArray(payload) ? payload : [];

                        if (oldCity && this.cities.some((city) => String(city.id) === String(oldCity))) {
                            this.selectedCity = String(oldCity);
                        }
                    } catch (error) {
                        this.cities = [];
                    } finally {
                        this.loadingCities = false;
                    }
                },
            };
        }
    </script>
@endsection
