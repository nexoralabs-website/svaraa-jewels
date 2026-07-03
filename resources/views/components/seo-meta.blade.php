@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'ogImage' => null,
    'ogType' => 'website',
    'schema' => null,
])

@php
    $seo = [
        'title'       => $title,
        'description' => $description,
        'canonical'   => $canonical,
        'og_image'    => $ogImage,
        'og_type'     => $ogType,
        'json_ld'     => $schema,
    ];
@endphp

@include('partials.seo-meta', ['seo' => $seo])
