@php
    $pickerId = $pickerId ?? ('locationPicker'.uniqid());
    $label = $label ?? 'Location';
    $textName = $textName ?? 'location_text';
    $textValue = $textValue ?? '';
    $cityName = $cityName ?? 'city';
    $cityValue = $cityValue ?? '';
    $latName = $latName ?? 'latitude';
    $latValue = $latValue ?? '';
    $lngName = $lngName ?? 'longitude';
    $lngValue = $lngValue ?? '';
    $placeIdName = $placeIdName ?? 'place_id';
    $placeIdValue = $placeIdValue ?? '';
    $precisionName = $precisionName ?? null;
    $precisionValue = $precisionValue ?? 'exact';
    $required = $required ?? false;
    $showPrecision = $showPrecision ?? false;
    $helpText = $helpText ?? null;
@endphp

<div class="space-y-2">
    <label for="{{ $pickerId }}_location_text" class="block text-sm font-semibold text-gray-700">{{ $label }}</label>
    <div class="relative">
        <input
            id="{{ $pickerId }}_location_text"
            name="{{ $textName }}"
            type="text"
            value="{{ $textValue }}"
            class="w-full border-gray-300 rounded-lg text-sm p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            placeholder="Search location or move the pin on map"
            autocomplete="off"
            @if($required) required @endif
        >
        <div id="{{ $pickerId }}_suggestions" class="hidden absolute left-0 right-0 mt-1 z-20 border border-gray-200 rounded-lg bg-white shadow-sm max-h-48 overflow-auto"></div>
    </div>

    <div id="{{ $pickerId }}_map" class="w-full h-64 rounded-lg border border-gray-200"></div>

    @if($showPrecision && $precisionName)
        <div>
            <label for="{{ $pickerId }}_precision" class="block text-sm font-medium text-gray-700 mb-1">Public Location Precision</label>
            <select
                id="{{ $pickerId }}_precision"
                name="{{ $precisionName }}"
                class="w-full border-gray-300 rounded-lg text-sm p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >
                <option value="approx" {{ $precisionValue === 'approx' ? 'selected' : '' }}>Approximate area only</option>
                <option value="exact" {{ $precisionValue === 'exact' ? 'selected' : '' }}>Exact pin</option>
            </select>
        </div>
    @endif

    <input type="hidden" id="{{ $pickerId }}_city" name="{{ $cityName }}" value="{{ $cityValue }}">
    <input type="hidden" id="{{ $pickerId }}_lat" name="{{ $latName }}" value="{{ $latValue }}">
    <input type="hidden" id="{{ $pickerId }}_lng" name="{{ $lngName }}" value="{{ $lngValue }}">
    <input type="hidden" id="{{ $pickerId }}_place_id" name="{{ $placeIdName }}" value="{{ $placeIdValue }}">

    @if($helpText)
        <p class="text-xs text-gray-500">{{ $helpText }}</p>
    @endif

    <x-input-error class="mt-1" :messages="$errors->get($textName)" />
    <x-input-error class="mt-1" :messages="$errors->get($latName)" />
    <x-input-error class="mt-1" :messages="$errors->get($lngName)" />
</div>

@once
    @push('scripts')
        <script>
            (function () {
                if (window.createLocationPicker) {
                    return;
                }

                const debounce = (fn, wait = 350) => {
                    let timeout;
                    return (...args) => {
                        clearTimeout(timeout);
                        timeout = setTimeout(() => fn(...args), wait);
                    };
                };

                window.createLocationPicker = function (config) {
                    const textInput = document.getElementById(config.ids.text);
                    const cityInput = document.getElementById(config.ids.city);
                    const latInput = document.getElementById(config.ids.lat);
                    const lngInput = document.getElementById(config.ids.lng);
                    const placeIdInput = document.getElementById(config.ids.placeId);
                    const suggestionsBox = document.getElementById(config.ids.suggestions);
                    const mapElement = document.getElementById(config.ids.map);

                    if (!textInput || !cityInput || !latInput || !lngInput || !placeIdInput || !suggestionsBox || !mapElement) {
                        return;
                    }

                    const hasInitialLatLng = latInput.value !== '' && lngInput.value !== '';
                    const initialLat = hasInitialLatLng ? parseFloat(latInput.value) : config.map.defaultLat;
                    const initialLng = hasInitialLatLng ? parseFloat(lngInput.value) : config.map.defaultLng;

                    const tileUrl = config.map.tileUrl || 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
                    const attribution = config.map.attribution || '&copy; OpenStreetMap contributors';
                    const map = L.map(mapElement).setView([initialLat, initialLng], config.map.defaultZoom);
                    L.tileLayer(tileUrl, {
                        attribution,
                        maxZoom: 19,
                    }).addTo(map);

                    let marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

                    const hideSuggestions = () => {
                        suggestionsBox.classList.add('hidden');
                        suggestionsBox.innerHTML = '';
                    };

                    const showSuggestions = () => {
                        if (suggestionsBox.innerHTML.trim() !== '') {
                            suggestionsBox.classList.remove('hidden');
                        }
                    };

                    const applyLocation = (row, moveMap = true, updateText = true) => {
                        if (typeof row.latitude !== 'number' || typeof row.longitude !== 'number') {
                            return;
                        }

                        latInput.value = row.latitude.toFixed(7);
                        lngInput.value = row.longitude.toFixed(7);
                        cityInput.value = row.city || '';
                        placeIdInput.value = row.place_id || '';
                        if (updateText) {
                            textInput.value = row.location_text || textInput.value;
                        }

                        marker.setLatLng([row.latitude, row.longitude]);
                        if (moveMap) {
                            const targetZoom = config.map.searchZoom || 15;
                            map.setView([row.latitude, row.longitude], Math.max(map.getZoom(), targetZoom));
                        }
                    };

                    const reverseGeocode = async (lat, lng, updateMapView = false) => {
                        const params = new URLSearchParams({
                            lat: String(lat),
                            lng: String(lng),
                        });

                        const response = await fetch(`${config.endpoints.reverse}?${params.toString()}`);
                        if (!response.ok) {
                            return;
                        }

                        const row = await response.json();
                        if (!row || typeof row !== 'object') {
                            return;
                        }

                        if (typeof row.latitude !== 'number') {
                            row.latitude = lat;
                        }
                        if (typeof row.longitude !== 'number') {
                            row.longitude = lng;
                        }

                        applyLocation(row, updateMapView);
                    };

                    marker.on('dragend', async (event) => {
                        const latLng = event.target.getLatLng();
                        await reverseGeocode(latLng.lat, latLng.lng);
                    });

                    map.on('click', async (event) => {
                        marker.setLatLng(event.latlng);
                        await reverseGeocode(event.latlng.lat, event.latlng.lng);
                    });

                    const searchByText = debounce(async () => {
                        const query = textInput.value.trim();
                        if (query.length < 2) {
                            hideSuggestions();
                            return;
                        }

                        const params = new URLSearchParams({
                            q: query,
                            limit: '5',
                        });

                        const response = await fetch(`${config.endpoints.search}?${params.toString()}`);
                        if (!response.ok) {
                            hideSuggestions();
                            return;
                        }

                        const rows = await response.json();
                        if (!Array.isArray(rows) || rows.length === 0) {
                            hideSuggestions();
                            return;
                        }

                        suggestionsBox.innerHTML = '';
                        rows.forEach((row) => {
                            const button = document.createElement('button');
                            button.type = 'button';
                            button.className = 'w-full text-left p-2 text-sm hover:bg-gray-50 border-b border-gray-100 last:border-b-0';
                            button.textContent = row.location_text || `${row.city || 'Unknown'}, ${row.latitude}, ${row.longitude}`;
                            button.addEventListener('click', () => {
                                applyLocation({
                                    location_text: row.location_text || '',
                                    city: row.city || '',
                                    latitude: parseFloat(row.latitude),
                                    longitude: parseFloat(row.longitude),
                                    place_id: row.place_id || '',
                                });
                                hideSuggestions();
                            });
                            suggestionsBox.appendChild(button);
                        });

                        suggestionsBox.classList.remove('hidden');
                    }, 350);

                    textInput.addEventListener('input', searchByText);
                    textInput.addEventListener('blur', () => setTimeout(hideSuggestions, 150));
                    textInput.addEventListener('focus', showSuggestions);
                    textInput.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') {
                            hideSuggestions();
                        }
                    });

                    document.addEventListener('click', (event) => {
                        if (event.target === textInput || suggestionsBox.contains(event.target)) {
                            return;
                        }
                        hideSuggestions();
                    });

                    if (hasInitialLatLng && textInput.value.trim() !== '') {
                        map.setView([initialLat, initialLng], Math.max(config.map.defaultZoom, 14));
                    }
                };
            })();
        </script>
    @endpush
@endonce

@push('scripts')
    <script>
        (() => {
            const init = () => {
                if (!window.createLocationPicker) {
                    return;
                }

                window.createLocationPicker({
                    ids: {
                        text: @json($pickerId.'_location_text'),
                        city: @json($pickerId.'_city'),
                        lat: @json($pickerId.'_lat'),
                        lng: @json($pickerId.'_lng'),
                        placeId: @json($pickerId.'_place_id'),
                        suggestions: @json($pickerId.'_suggestions'),
                        map: @json($pickerId.'_map'),
                    },
                    map: {
                        tileUrl: @json(config('services.maps.tile_url') ?: config('services.maps.fallback_tile_url')),
                        attribution: @json(config('services.maps.tile_attribution') ?: config('services.maps.fallback_tile_attribution')),
                        defaultLat: @json(config('services.maps.default_lat')),
                        defaultLng: @json(config('services.maps.default_lng')),
                        defaultZoom: @json(config('services.maps.default_zoom')),
                        searchZoom: 15,
                    },
                    endpoints: {
                        search: @json(route('geocode.search')),
                        reverse: @json(route('locations.reverse')),
                    },
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init, { once: true });
            } else {
                init();
            }
        })();
    </script>
@endpush
