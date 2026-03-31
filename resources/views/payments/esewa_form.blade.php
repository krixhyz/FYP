@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-10 px-6">
    <div class="surface-card-strong p-6 text-center space-y-4">
        <p class="section-kicker">Payment</p>
        <h2 class="section-title mt-1">Redirecting to eSewa</h2>
        <p class="font-manrope text-sm text-[#444746]">Please wait while we redirect you to the payment page.</p>

        <form id="esewa-payment-form" action="{{ $formUrl }}" method="POST">
            @foreach($payload as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <noscript>
                <button type="submit" class="btn-pill btn-pill-dark mt-4 w-full justify-center">
                    Continue to eSewa
                </button>
            </noscript>
        </form>
    </div>
</div>

<script>
    document.getElementById('esewa-payment-form').submit();
</script>
@endsection
