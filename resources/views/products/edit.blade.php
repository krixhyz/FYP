<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 text-center"> Edit Listing</h2>
    </x-slot>

    <div class="max-w-lg mx-auto mt-8 bg-white shadow-lg rounded-2xl p-6 border border-gray-100">
        <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @method('PUT')
            @include('products.form', ['buttonText' => 'Update Listing'])
        </form>
    </div>
</x-app-layout>
