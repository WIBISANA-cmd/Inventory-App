@props(['sisaStok' => 0, 'batasMinimum' => 0])

@php
    $sisaStok = (float) $sisaStok;
    $batasMinimum = (float) $batasMinimum;

    if ($sisaStok > $batasMinimum) {
        [$label, $variant] = ['AMAN', 'success'];
    } elseif ($sisaStok > 0) {
        [$label, $variant] = ['MENIPIS', 'warning'];
    } else {
        [$label, $variant] = ['HABIS', 'danger'];
    }
@endphp

<span {{ $attributes->merge(['class' => "badge-solid badge-solid-{$variant}"]) }}>{{ $label }}</span>
