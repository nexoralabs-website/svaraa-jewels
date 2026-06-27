@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'ogImage' => null,
    'schema' => null,
])

@php
    $seo = [
        'title' => $title,
        'description' => $description,
        'canonical' => $canonical,
        'og_image' => $ogImage,
        'json_ld' => $schema,
    ];
@endphp

@include('partials.seo-meta', ['seo' => $seo])
