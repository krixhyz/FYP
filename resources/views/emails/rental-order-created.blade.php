<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #006a38; color: white; padding: 20px; text-align: center; margin-bottom: 20px; border-radius: 4px; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { background-color: #f9f9f9; padding: 20px; border-radius: 4px; }
        h2 { color: #006a38; margin-bottom: 20px; }
        h3 { color: #006a38; margin-top: 25px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        tr { border: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f3f3f3; }
        td { padding: 12px; text-align: left; }
        td:first-child { font-weight: bold; width: 30%; }
        .footer { margin-top: 30px; padding: 15px; background-color: #f3f3f3; border-left: 4px solid #006a38; border-radius: 4px; }
        .footer p { margin: 0; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>

        <div class="content">
            <h2>
                @if($role === 'owner')
                    New Rental Order Received
                @else
                    Your Rental Order Is Confirmed
                @endif
            </h2>

            <h3>Rental Details</h3>
            <table>
                <tr>
                    <td>Product</td>
                    <td>{{ optional($rentalRequest->product)->title ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Start Date</td>
                    <td>{{ optional($rentalRequest->start_date)->format('M d, Y') ?? $rentalRequest->start_date }}</td>
                </tr>
                <tr>
                    <td>End Date</td>
                    <td>{{ optional($rentalRequest->end_date)->format('M d, Y') ?? $rentalRequest->end_date }}</td>
                </tr>
                <tr>
                    <td>Duration</td>
                    <td>{{ $rentalRequest->duration }} day(s)</td>
                </tr>
                <tr>
                    <td>Rent Amount</td>
                    <td>Rs. {{ number_format((float) $rentalRequest->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Deposit</td>
                    <td>Rs. {{ number_format((float) ($rentalRequest->rent_deposit ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td>Payment Status</td>
                    <td>{{ ucfirst($rentedRental->payment_status) }}</td>
                </tr>
                <tr>
                    <td>Reference</td>
                    <td>{{ $rentedRental->payment_reference ?? 'N/A' }}</td>
                </tr>
            </table>

            <h3>
                @if($role === 'owner')
                    Renter Details
                @else
                    Owner Details
                @endif
            </h3>
            <table>
                <tr>
                    <td>Name</td>
                    <td>
                        @if($role === 'owner')
                            {{ optional($rentalRequest->renter)->name ?? 'N/A' }}
                        @else
                            {{ optional($rentalRequest->owner)->name ?? 'N/A' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Phone</td>
                    <td>
                        @if($role === 'owner')
                            {{ optional($rentalRequest->renter)->phone_number ?? 'Not provided' }}
                        @else
                            {{ optional($rentalRequest->owner)->phone_number ?? 'Not provided' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>
                        @if($role === 'owner')
                            {{ optional($rentalRequest->renter)->email ?? 'N/A' }}
                        @else
                            {{ optional($rentalRequest->owner)->email ?? 'N/A' }}
                        @endif
                    </td>
                </tr>
            </table>

            <div class="footer">
                <p>
                    @if($role === 'owner')
                        Please coordinate with the renter for handover and return logistics.
                    @else
                        Please coordinate with the owner for item handover and return logistics.
                    @endif
                </p>
            </div>

            <div style="margin-top: 40px; text-align: center; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #888;">
                <p style="margin: 5px 0;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
