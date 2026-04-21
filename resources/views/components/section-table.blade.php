@props(['data', 'empty' => 'No records found.'])

<table class="w-full border-collapse text-left text-sm bg-[#f3f3f3]">
    <thead class="bg-[#f3f3f3]">
        <tr>
            {{ $header }}
        </tr>
    </thead>

    <tbody>
        @if ($data->isEmpty())
            <tr>
                <td colspan="10" class="py-6 text-center text-[#444746] font-manrope">{{ $empty }}</td>
            </tr>
        @else
            {{ $slot }}
        @endif
    </tbody>
</table>
