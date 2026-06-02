@extends('layouts.app')
@section('title', 'Cari Apotek Terdekat')

@push('head')
{{-- Google Maps Places API --}}
@if($mapsApiKey)
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ $mapsApiKey }}&libraries=places&callback=initMap">
    </script>
@endif
<style>
    #map { height: 420px; border-radius: 0.75rem; overflow: hidden; }
    .apotek-card { transition: all 0.2s; }
    .apotek-card:hover { box-shadow: 0 6px 20px -4px rgba(0,0,0,0.12); transform: translateY(-1px); }
    .apotek-card.active-card { border-color: #185FA5; box-shadow: 0 0 0 2px rgba(24,95,165,0.2); }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="mb-8 animate-fade-in-up">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-[#EF9F27] mb-2">
                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Cari Apotek
            </span>
            <h1 class="text-3xl font-bold text-[#042C53]">Apotek Terdekat</h1>
            <p class="text-gray-500 mt-1">Temukan apotek di dekat Anda untuk mengisi kembali stok obat.</p>
        </div>
        <div class="flex items-center gap-3">
            <button id="btn-my-location"
                    onclick="locateMe()"
                    class="obk-btn obk-btn-outline flex items-center gap-2">
                <i data-lucide="locate" class="w-4 h-4"></i>
                Gunakan Lokasi Saya
            </button>
        </div>
    </div>
</div>

{{-- Search Bar --}}
<div class="mb-6 animate-fade-in-up" style="animation-delay: 0.05s">
    <div class="flex gap-3">
        <div class="relative flex-1">
            <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
            <input type="text"
                   id="search-input"
                   placeholder="Cari nama apotek atau masukkan alamat / kota..."
                   class="obk-input pl-10 pr-4 w-full">
        </div>
        <button onclick="searchNearby()"
                id="btn-search"
                class="obk-btn obk-btn-primary flex items-center gap-2 px-6">
            <i data-lucide="search" class="w-4 h-4"></i>
            Cari
        </button>
    </div>
</div>

@if(!$mapsApiKey)
    {{-- No API Key —  Fallback notice --}}
    <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-[#EF9F27] shrink-0 mt-0.5"></i>
        <div>
            <p class="text-sm font-bold text-amber-800">Google Maps API Key Belum Dikonfigurasi</p>
            <p class="text-xs text-amber-700 mt-1">
                Tambahkan <code class="bg-amber-100 px-1 rounded">GOOGLE_MAPS_KEY=your_key</code> ke file <code class="bg-amber-100 px-1 rounded">.env</code>
                dan pastikan Places API aktif di Google Cloud Console.
            </p>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 animate-fade-in-up" style="animation-delay: 0.1s">

    {{-- Map --}}
    <div class="lg:col-span-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div id="map" class="w-full">
                @if(!$mapsApiKey)
                    {{-- Fallback static map placeholder --}}
                    <div class="h-[420px] bg-gray-100 flex flex-col items-center justify-center text-gray-400">
                        <i data-lucide="map" class="w-16 h-16 mb-3"></i>
                        <p class="text-sm font-semibold">Peta tidak tersedia</p>
                        <p class="text-xs mt-1">Konfigurasi Google Maps API Key untuk mengaktifkan fitur ini</p>
                        <a href="https://www.google.com/maps/search/apotek+terdekat" target="_blank" rel="noopener"
                           class="mt-4 text-xs font-bold text-[#185FA5] hover:underline flex items-center gap-1">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            Buka Google Maps ↗
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Google Maps Attribution --}}
        @if($mapsApiKey)
        <p class="text-xs text-gray-400 mt-2 text-center">Data apotek disediakan oleh Google Maps Places API</p>
        @endif
    </div>

    {{-- Results List --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 h-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-[#042C53] flex items-center gap-2">
                    <i data-lucide="list" class="w-4 h-4 text-[#185FA5]"></i>
                    Hasil Pencarian
                </h3>
                <span id="results-count" class="text-xs text-gray-400 font-semibold"></span>
            </div>

            <div id="results-list" class="space-y-3 overflow-y-auto max-h-[360px] pr-1">
                {{-- Placeholder state --}}
                <div id="placeholder-state" class="text-center py-8 text-gray-400">
                    <i data-lucide="map-pin" class="w-10 h-10 mx-auto mb-2"></i>
                    <p class="text-sm font-semibold">Gunakan tombol "Lokasi Saya"</p>
                    <p class="text-xs mt-1">atau ketik alamat untuk mencari apotek terdekat</p>
                </div>
                {{-- Loading state --}}
                <div id="loading-state" class="hidden text-center py-8 text-gray-400">
                    <div class="w-8 h-8 border-2 border-[#185FA5] border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                    <p class="text-sm font-semibold">Mencari apotek terdekat...</p>
                </div>
                {{-- Dynamic cards injected here by JS --}}
            </div>
        </div>
    </div>

</div>

{{-- Info Banner --}}
<div class="mt-6 p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-3 animate-fade-in-up" style="animation-delay: 0.15s">
    <i data-lucide="info" class="w-5 h-5 text-[#185FA5] shrink-0 mt-0.5"></i>
    <p class="text-sm text-blue-800">
        <strong>Tips:</strong> Saat stok obat Anda habis, fitur ini membantu menemukan apotek terdekat dengan cepat.
        Klik <strong>"Navigasi"</strong> untuk membuka rute di Google Maps.
    </p>
</div>

@endsection

@push('head')
<script>
let map, service, infoWindow;
const markers = [];

// ── Initialize Map ───────────────────────────────────────────────────────────
function initMap() {
    const defaultCenter = { lat: -6.2088, lng: 106.8456 }; // Jakarta default

    map = new google.maps.Map(document.getElementById('map'), {
        center: defaultCenter,
        zoom: 13,
        styles: [
            { featureType: 'poi', elementType: 'labels', stylers: [{ visibility: 'off' }] },
            { featureType: 'transit', stylers: [{ visibility: 'simplified' }] },
        ],
    });

    service = new google.maps.places.PlacesService(map);
    infoWindow = new google.maps.InfoWindow();

    // Auto-locate on load
    locateMe();
}

// ── Locate User ──────────────────────────────────────────────────────────────
function locateMe() {
    if (!navigator.geolocation) {
        alert('Geolokasi tidak didukung oleh browser Anda.');
        return;
    }
    showLoading(true);
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const location = { lat: pos.coords.latitude, lng: pos.coords.longitude };
            map.setCenter(location);
            map.setZoom(14);

            new google.maps.Marker({
                position: location,
                map,
                title: 'Lokasi Saya',
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 8,
                    fillColor: '#185FA5',
                    fillOpacity: 1,
                    strokeColor: '#fff',
                    strokeWeight: 2,
                },
            });

            searchPharmaciesNear(location);
        },
        (err) => {
            showLoading(false);
            console.warn('Geolocation error:', err.message);
            showError('Tidak dapat mengakses lokasi. Pastikan izin lokasi diberikan.');
        },
        { timeout: 10000 }
    );
}

// ── Search by text input ─────────────────────────────────────────────────────
function searchNearby() {
    const query = document.getElementById('search-input').value.trim();
    if (!query) { locateMe(); return; }

    showLoading(true);
    clearMarkers();

    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: query + ', Indonesia' }, (results, status) => {
        if (status === 'OK' && results[0]) {
            const loc = results[0].geometry.location;
            map.setCenter(loc);
            map.setZoom(14);
            searchPharmaciesNear({ lat: loc.lat(), lng: loc.lng() });
        } else {
            showLoading(false);
            showError('Lokasi tidak ditemukan. Coba kata kunci lain.');
        }
    });
}

// ── Core Places API Query ────────────────────────────────────────────────────
function searchPharmaciesNear(location) {
    const request = {
        location,
        radius: 3000,
        type: ['pharmacy'],
        keyword: 'apotek',
    };

    service.nearbySearch(request, (results, status) => {
        showLoading(false);
        clearMarkers();

        if (status !== google.maps.places.PlacesServiceStatus.OK || !results.length) {
            showError('Tidak ada apotek ditemukan dalam radius 3 km.');
            return;
        }

        renderResults(results, location);
    });
}

// ── Render result cards + markers ────────────────────────────────────────────
function renderResults(places, userLocation) {
    const list = document.getElementById('results-list');
    document.getElementById('placeholder-state').classList.add('hidden');
    document.getElementById('loading-state').classList.add('hidden');
    document.getElementById('results-count').textContent = places.length + ' apotek ditemukan';

    // Remove old cards (keep placeholder/loading divs)
    list.querySelectorAll('.apotek-card').forEach(el => el.remove());

    places.forEach((place, idx) => {
        // Distance calculation (Haversine approx)
        const distKm = userLocation ? haversine(
            userLocation.lat, userLocation.lng,
            place.geometry.location.lat(), place.geometry.location.lng()
        ) : null;

        // Marker
        const marker = new google.maps.Marker({
            position: place.geometry.location,
            map,
            title: place.name,
            label: { text: String(idx + 1), color: '#fff', fontWeight: 'bold', fontSize: '11px' },
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 16,
                fillColor: '#185FA5',
                fillOpacity: 1,
                strokeColor: '#fff',
                strokeWeight: 2,
            },
        });

        markers.push(marker);

        marker.addListener('click', () => {
            highlightCard(idx);
            infoWindow.setContent(`
                <div style="font-family:Inter,sans-serif;padding:4px;max-width:200px">
                    <strong style="color:#042C53;font-size:13px">${place.name}</strong><br>
                    <span style="font-size:11px;color:#666">${place.vicinity || ''}</span><br>
                    ${place.rating ? `<span style="color:#EF9F27;font-size:11px">★ ${place.rating}</span>` : ''}
                </div>
            `);
            infoWindow.open(map, marker);
        });

        // Card
        const isOpen = place.opening_hours?.isOpen?.() ?? null;
        const ratingStars = place.rating ? `★ ${place.rating.toFixed(1)}` : '';
        const openBadge = isOpen === null
            ? ''
            : isOpen
                ? '<span class="text-xs font-bold text-[#1D9E75] bg-green-50 px-2 py-0.5 rounded-full">Buka</span>'
                : '<span class="text-xs font-bold text-[#E24B4A] bg-red-50 px-2 py-0.5 rounded-full">Tutup</span>';

        const mapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(place.name)}&destination_place_id=${place.place_id}`;

        const card = document.createElement('div');
        card.className = 'apotek-card border border-gray-100 rounded-xl p-4 cursor-pointer';
        card.id = `apotek-card-${idx}`;
        card.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-[#185FA5] flex items-center justify-center text-white text-xs font-bold shrink-0">${idx + 1}</div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-[#042C53] truncate">${place.name}</p>
                    <p class="text-xs text-gray-400 mt-0.5 truncate">${place.vicinity || ''}</p>
                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                        ${openBadge}
                        ${ratingStars ? `<span class="text-xs text-[#EF9F27] font-semibold">${ratingStars}</span>` : ''}
                        ${distKm !== null ? `<span class="text-xs text-gray-400">${distKm.toFixed(1)} km</span>` : ''}
                    </div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-50 flex gap-2">
                <a href="${mapsUrl}" target="_blank" rel="noopener"
                   class="flex-1 text-center text-xs font-bold py-2 rounded-lg bg-[#185FA5] text-white hover:bg-[#042C53] transition-colors flex items-center justify-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                    Navigasi
                </a>
            </div>
        `;
        card.addEventListener('click', () => {
            map.setCenter(place.geometry.location);
            map.setZoom(16);
            google.maps.event.trigger(marker, 'click');
            highlightCard(idx);
        });
        list.appendChild(card);
    });
}

// ── Helpers ──────────────────────────────────────────────────────────────────
function highlightCard(idx) {
    document.querySelectorAll('.apotek-card').forEach(c => c.classList.remove('active-card'));
    const card = document.getElementById(`apotek-card-${idx}`);
    if (card) {
        card.classList.add('active-card');
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function clearMarkers() {
    markers.forEach(m => m.setMap(null));
    markers.length = 0;
    document.querySelectorAll('.apotek-card').forEach(el => el.remove());
    document.getElementById('results-count').textContent = '';
}

function showLoading(show) {
    document.getElementById('placeholder-state').classList.add('hidden');
    document.getElementById('loading-state').classList.toggle('hidden', !show);
}

function showError(msg) {
    const list = document.getElementById('results-list');
    list.querySelectorAll('.apotek-card').forEach(el => el.remove());
    const err = document.createElement('div');
    err.className = 'apotek-card text-center py-8 text-gray-400';
    err.innerHTML = `<i data-lucide="search-x" class="w-8 h-8 mx-auto mb-2"></i><p class="text-sm">${msg}</p>`;
    list.appendChild(err);
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function haversine(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

// Allow pressing Enter in search input
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('search-input')?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') searchNearby();
    });
});
</script>
@endpush
