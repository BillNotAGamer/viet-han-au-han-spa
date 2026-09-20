@props([
    'inverse' => false,
])

<p {{ $attributes->class(['v2-type-eyebrow', 'v2-type-eyebrow--inverse' => $inverse]) }}>
    {{ $slot }}
</p>
