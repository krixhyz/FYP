@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-lg p-4 mx-auto" style="max-width: 600px;">
        <h2 class="text-center mb-4">Rental Checkout</h2>

        @php
            $rentType = $rentalRequest->rental?->rent_type ?? 'daily';
            $rentFare = $rentalRequest->rental?->rent_fare ?? 0;
            $rentDeposit = $rentalRequest->rent_deposit ?? ($rentalRequest->rental?->rent_deposit ?? 0);
            $totalAmount = ($rentalRequest->total_amount ?? 0) + $rentDeposit;
        @endphp

        <table class="table table-bordered">
            <tr>
                <th>Product</th>
                <td>{{ $rentalRequest->product->title }}</td>
            </tr>
            <tr>
                <th>Rent Type</th>
                <td>{{ ucfirst($rentType) }}</td>
            </tr>
            <tr>
                <th>Duration</th>
                <td>{{ $rentalRequest->duration }} {{ $rentType == 'hourly' ? 'hours' : 'days' }}</td>
            </tr>
            <tr>
                <th>Fare</th>
                <td>Rs. {{ number_format($rentFare, 2) }}</td>
            </tr>
            <tr>
                <th>Deposit</th>
                <td>Rs. {{ number_format($rentDeposit, 2) }}</td>
            </tr>
            <tr class="table-info">
                <th>Total Amount</th>
                <td><strong>Rs. {{ number_format($totalAmount, 2) }}</strong></td>
            </tr>
        </table>

        <form method="POST" action="{{ route('rental.pay', $rentalRequest->id) }}" class="mt-3">
            @csrf
            <button type="submit" class="btn btn-primary w-100">Pay with eSewa</button>
        </form>

        <a href="{{ route('products.index') }}" class="btn btn-success w-100 mt-3">Back to Products</a>
    </div>
</div>
@endsection
