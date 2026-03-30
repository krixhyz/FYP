<section>
    <header>
        <h2 class="text-lg font-extrabold tracking-tight text-[var(--reloop-ink)]">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-[var(--reloop-ink-soft)]">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
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
</section>
