@props(['class' => '', 'lines' => 1, 'type' => 'block'])

@if($type === 'product-card')
{{-- Product card skeleton --}}
<div {{ $attributes->merge(['class' => 'animate-pulse bg-white rounded-xl border border-gray-100 overflow-hidden ' . $class]) }}>
    <div class="aspect-square bg-gray-200"></div>
    <div class="p-4 space-y-2">
        <div class="h-3 bg-gray-200 rounded w-1/3"></div>
        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
        <div class="h-4 bg-gray-200 rounded w-1/4 mt-2"></div>
    </div>
</div>
@elseif($type === 'text')
{{-- Text line skeleton --}}
<div {{ $attributes->merge(['class' => 'animate-pulse space-y-2 ' . $class]) }}>
    @for($i = 0; $i < $lines; $i++)
    <div class="h-4 bg-gray-200 rounded {{ $loop->last ? 'w-2/3' : 'w-full' }}"></div>
    @endfor
</div>
@elseif($type === 'order-row')
{{-- Order list row skeleton --}}
<div {{ $attributes->merge(['class' => 'animate-pulse flex items-center gap-4 p-4 ' . $class]) }}>
    <div class="w-16 h-16 bg-gray-200 rounded-lg flex-shrink-0"></div>
    <div class="flex-1 space-y-2">
        <div class="h-4 bg-gray-200 rounded w-1/3"></div>
        <div class="h-3 bg-gray-200 rounded w-1/4"></div>
    </div>
    <div class="h-5 bg-gray-200 rounded w-20"></div>
</div>
@else
{{-- Default block skeleton --}}
<div {{ $attributes->merge(['class' => 'animate-pulse bg-gray-200 rounded ' . $class]) }}></div>
@endif
