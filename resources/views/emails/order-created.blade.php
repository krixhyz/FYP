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
            <h2>New Order Received</h2>
            <p>You have received a new order! Here are the details:</p>

            <h3>Order Details</h3>
            <table>
                <tr>
                    <td>Product</td>
                    <td>{{ $order->product->title }}</td>
                </tr>
                <tr>
                    <td>Quantity</td>
                    <td>{{ $order->quantity }}</td>
                </tr>
                <tr>
                    <td>Unit Price</td>
                    <td>Rs. {{ number_format($order->unit_price, 2) }}</td>
                </tr>
                <tr>
                    <td>Total Amount</td>
                    <td><strong>Rs. {{ number_format($order->total_price, 2) }}</strong></td>
                </tr>
            </table>

            <h3>Buyer Details</h3>
            <table>
                <tr>
                    <td>Name</td>
                    <td>{{ $order->buyer_name }}</td>
                </tr>
                <tr>
                    <td>Phone</td>
                    <td>{{ $order->buyer_phone }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>{{ $order->buyer_email }}</td>
                </tr>
                <tr>
                    <td>Address</td>
                    <td>{{ $order->buyer_address }}</td>
                </tr>
            </table>

            <div class="footer">
                <p>Please contact the buyer at {{ $order->buyer_phone }} or {{ $order->buyer_email }} to confirm delivery and arrange logistics.</p>
            </div>
            
            <div style="margin-top: 40px; text-align: center; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #888;">
                <p style="margin: 5px 0;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
