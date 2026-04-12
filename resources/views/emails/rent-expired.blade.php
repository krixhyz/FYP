<x-mail::message>
# Your Rental Has Expired

Hi {{ $notifiable->name }},

Your rental for **{{ $rental->product->title }}** has now expired (ended on **{{ $rental->end_date->format('M d, Y') }}**).

If you haven't already, please return the item to the owner as soon as possible.

<x-mail::button :url="route('rental.show', $rental->id)">
View Rental Details
</x-mail::button>

Thank you for using our platform!

Best regards,  
{{ config('app.name') }}
</x-mail::message>
