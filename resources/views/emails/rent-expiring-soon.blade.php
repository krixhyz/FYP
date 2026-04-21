<x-mail::message>
# Your Rental is Expiring Tomorrow!

Hi {{ $notifiable->name }},

Your rental for **{{ $rental->product->title }}** will expire tomorrow (**{{ $rental->end_date->format('M d, Y') }}**).

Please make sure to return the item by the specified date to avoid any issues.

<x-mail::button :url="route('rental.show', $rental->id)">
View Rental Details
</x-mail::button>

Thank you for renting with us!

Best regards,  
{{ config('app.name') }}
</x-mail::message>
