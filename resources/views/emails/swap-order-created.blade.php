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
        .status-badge { display: inline-block; padding: 5px 10px; background-color: #006a38; color: white; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }} - Swap Offer</h1>
        </div>

        <div class="content">
            <h2>
                @if($role === 'owner')
                    You Received a Swap Offer
                @else
                    Your Swap Request Details
                @endif
            </h2>

            <h3>Swap Overview</h3>
            <table>
                <tr>
                    <td>Swap Status</td>
                    <td><span class="status-badge">{{ ucfirst(str_replace('_', ' ', $swapRequest->status)) }}</span></td>
                </tr>
                <tr>
                    <td>Initiated</td>
                    <td>{{ $swapRequest->created_at->format('M d, Y \a\t g:i A') }}</td>
                </tr>
            </table>

            @if($role === 'owner')
                <h3>Their Item (They're Offering)</h3>
                <table>
                    <tr>
                        <td>Product</td>
                        <td>{{ optional($swapRequest->offeredProduct)->title ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Description</td>
                        <td>{{ optional($swapRequest->offeredProduct)->description ?? 'N/A' }}</td>
                    </tr>
                </table>

                <h3>Your Item (They're Requesting)</h3>
                <table>
                    <tr>
                        <td>Product</td>
                        <td>{{ $swapRequest->product->title }}</td>
                    </tr>
                    <tr>
                        <td>Your Item Value</td>
                        <td>Rs. {{ number_format($swapRequest->product->price, 2) }}</td>
                    </tr>
                </table>
            @else
                <h3>Your Item (You're Offering)</h3>
                <table>
                    <tr>
                        <td>Product</td>
                        <td>{{ optional($swapRequest->offeredProduct)->title ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Description</td>
                        <td>{{ optional($swapRequest->offeredProduct)->description ?? 'N/A' }}</td>
                    </tr>
                </table>

                <h3>Item They're Trading (You're Requesting)</h3>
                <table>
                    <tr>
                        <td>Product</td>
                        <td>{{ $swapRequest->product->title }}</td>
                    </tr>
                    <tr>
                        <td>Item Value</td>
                        <td>Rs. {{ number_format($swapRequest->product->price, 2) }}</td>
                    </tr>
                </table>
            @endif

            @if($swapRequest->money_direction !== 'none')
                <h3>Cash Component</h3>
                <table>
                    <tr>
                        <td>Money Direction</td>
                        <td>
                            @if($swapRequest->money_direction === 'owner_asks_cash')
                                Owner asking for additional cash
                            @elseif($swapRequest->money_direction === 'requester_offers_cash')
                                Requester offering additional cash
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Amount</td>
                        <td>Rs. {{ number_format($swapRequest->asking_amount ?? $swapRequest->offered_amount, 2) }}</td>
                    </tr>
                </table>
            @endif

            @if($swapRequest->message)
                <h3>Message</h3>
                <p>{{ $swapRequest->message }}</p>
            @endif

            <h3>
                @if($role === 'owner')
                    Their Contact Info
                @else
                    Recipient Contact Info
                @endif
            </h3>
            <table>
                <tr>
                    <td>Name</td>
                    <td>
                        @if($role === 'owner')
                            {{ $swapRequest->requester->name }}
                        @else
                            {{ $swapRequest->owner->name }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Phone</td>
                    <td>
                        @if($role === 'owner')
                            {{ $swapRequest->requester->phone_number ?? 'Not provided' }}
                        @else
                            {{ $swapRequest->owner->phone_number ?? 'Not provided' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>
                        @if($role === 'owner')
                            {{ $swapRequest->requester->email }}
                        @else
                            {{ $swapRequest->owner->email }}
                        @endif
                    </td>
                </tr>
            </table>

            <div class="footer">
                <p>
                    @if($role === 'owner')
                        <strong>Next Step:</strong> Review this offer and respond via the {{ config('app.name') }} app. 
                        You can accept, counter-offer, or decline this swap.
                    @else
                        <strong>Next Step:</strong> Awaiting their response to your swap request. 
                        You'll be notified when they accept, make a counter-offer, or decline.
                    @endif
                </p>
                <p style="margin-top: 10px; font-size: 12px;">
                    All communication regarding this swap should happen through {{ config('app.name') }} app for your safety and protection.
                </p>
            </div>

            <div style="margin-top: 40px; text-align: center; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #888;">
                <p style="margin: 5px 0;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
