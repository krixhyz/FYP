<section class="space-y-6" x-data="{ open: @json($errors->userDeletion->isNotEmpty()) }" x-cloak>
    @php
        $deleteAccountAction = $deleteAccountAction ?? route('profile.destroy');
    @endphp

    <header>
        <h2 class="text-lg font-extrabold tracking-tight text-[var(--reloop-ink)]">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-[var(--reloop-ink-soft)]">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button x-on:click.prevent="open = true" class="btn-pill bg-[var(--reloop-danger)] text-white border-[var(--reloop-danger)] hover:bg-[#b91c1c] hover:border-[#b91c1c]">
        {{ __('Delete Account') }}
    </button>

    <!-- Modal -->
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
        aria-modal="true" role="dialog"
    >
        <div class="fixed inset-0 bg-black/50" x-on:click="open = false"></div>

        <div class="relative w-full max-w-2xl border border-[var(--reloop-border)] bg-white p-6 shadow-[10px_10px_0_var(--reloop-shadow)]" x-transition.scale>
            <form method="post" action="{{ $deleteAccountAction }}">
                @csrf
                @method('delete')

                <h2 class="text-lg font-extrabold tracking-tight text-[var(--reloop-ink)]">
                    {{ __('Are you sure you want to delete your account?') }}
                </h2>

                <p class="mt-1 text-sm text-[var(--reloop-ink-soft)]">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>

                <div class="mt-6">
                    <label for="password" class="field-label">{{ __('Password') }}</label>
                    <input id="password" name="password" type="password" class="input-field" placeholder="{{ __('Password') }}" autocomplete="current-password" />
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" x-on:click="open = false" class="btn-pill btn-pill-soft">{{ __('Cancel') }}</button>

                    <button type="submit" class="ms-3 btn-pill bg-[var(--reloop-danger)] text-white border-[var(--reloop-danger)] hover:bg-[#b91c1c] hover:border-[#b91c1c]">{{ __('Delete Account') }}</button>
                </div>
            </form>
        </div>
    </div>
</section>
