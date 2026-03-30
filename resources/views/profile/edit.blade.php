@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <section class="surface-card-strong p-6 sm:p-8">
        <p class="section-kicker">Account</p>
        <h1 class="section-title mt-1">Profile Settings</h1>
        <p class="meta-text mt-2">Manage account details, password, and account lifecycle controls.</p>
    </section>

    <section class="surface-card p-6 sm:p-8">
        <div class="max-w-2xl">
            @include('profile.partials.update-profile-information-form')
        </div>
    </section>

    <section class="surface-card p-6 sm:p-8">
        <div class="max-w-2xl">
            @include('profile.partials.update-password-form')
        </div>
    </section>

    <section class="surface-card p-6 sm:p-8">
        <div class="max-w-2xl">
            @include('profile.partials.delete-user-form')
        </div>
    </section>
</div>
@endsection