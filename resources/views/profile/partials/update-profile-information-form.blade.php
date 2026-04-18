<section>
    <header>
        <h2 class="text-lg font-extrabold tracking-tight text-[var(--reloop-ink)]">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-[var(--reloop-ink-soft)]">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    @php
        $profileUpdateAction = $profileUpdateAction ?? route('profile.update');
    @endphp

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ $profileUpdateAction }}" class="mt-6 space-y-6" x-data="locationForm()">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="field-label">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" class="input-field" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="field-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="input-field" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-[var(--reloop-ink-soft)]">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="text-sm font-bold text-[var(--reloop-primary)] hover:text-[var(--reloop-primary-dark)]">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-semibold text-[var(--reloop-primary-dark)]">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="province_id" class="field-label">Province</label>
                <select id="province_id" name="province_id" class="input-field" x-model="provinceId" @change="loadCities()" required>
                    <option value="">Select Province</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province->id }}" @selected((string) old('province_id', $user->province_id) === (string) $province->id)>
                            {{ $province->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('province_id')" />
            </div>

            <div>
                <label for="city_id" class="field-label">City</label>
                <div class="relative">
                    <select id="city_id" name="city_id" class="input-field pr-9" :disabled="!provinceId || loadingCities" required>
                        <option value="" x-text="provinceId ? 'Select City' : 'Select Province first'"></option>
                        <template x-for="city in cities" :key="city.id">
                            <option :value="city.id" x-text="city.name" :selected="String(selectedCityId) === String(city.id)"></option>
                        </template>
                    </select>
                    <span x-show="loadingCities" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-neutral-500">Loading...</span>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('city_id')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-pill btn-pill-dark">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-[var(--reloop-ink-soft)]"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>

    <script>
        function locationForm() {
            return {
                provinceId: '{{ old('province_id', $user->province_id) }}',
                selectedCityId: '{{ old('city_id', $user->city_id) }}',
                loadingCities: false,
                cities: @json($cities),

                async loadCities() {
                    if (!this.provinceId) {
                        this.cities = [];
                        this.selectedCityId = '';
                        return;
                    }

                    this.loadingCities = true;
                    try {
                        const response = await fetch(`/api/cities/${this.provinceId}`);
                        const payload = await response.json();
                        this.cities = Array.isArray(payload) ? payload : [];
                    } catch (e) {
                        this.cities = [];
                    } finally {
                        this.loadingCities = false;
                    }
                }
            }
        }
    </script>
</section>
