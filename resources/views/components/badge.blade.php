{{--
    Komponen Blade: Badge Status Stok Produk
    Menerima prop 'status' (aman, menipis, habis) dan menerapkan styling Tailwind secara dinamis.
--}}
@props([
    'status' => 'aman',
])

@php
    // 1. Normalisasi status ke huruf kecil untuk konsistensi pencocokan
    $normalizedStatus = strtolower((string) $status);

    // 2. Pemetaan kelas visual Tailwind berdasarkan status menggunakan PHP match expression
    $classes = match ($normalizedStatus) {
        'aman'    => 'bg-green-100 text-green-800 border border-green-200',
        'menipis' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
        'habis'   => 'bg-red-100 text-red-800 border border-red-200',
        default   => 'bg-gray-100 text-gray-800 border border-gray-200',
    };
@endphp

{{-- 3. Render elemen badge dengan class dinamis dan teks slot / default status --}}
<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$classes}"]) }}>
    {{ $slot->isEmpty() ? ucfirst($status) : $slot }}
</span>