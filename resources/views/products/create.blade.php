@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-8">
    <section class="surface-card-strong p-6 sm:p-8">
        <p class="section-kicker">Seller Workspace</p>
        <h1 class="section-title mt-1">Add New Listing</h1>
    </section>

    <section class="surface-card p-5 sm:p-6">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @include('products.form', ['buttonText' => 'Add Listing'])
        </form>
    </section>
</div>
@endsection
