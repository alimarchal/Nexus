@props(['value' => null])

@php
    // A single leading "*" or a trailing " *" marks a mandatory field: show it once, in red, after the label.
    $text = is_string($value) ? $value : null;
    $isRequired = false;

    if ($text !== null && preg_match('/^\*(?!\*)/', $text) === 1) {
        $text = ltrim(substr($text, 1));
        $isRequired = true;
    }

    if ($text !== null && preg_match('/\s\*$/', $text) === 1) {
        $text = rtrim(substr($text, 0, -1));
        $isRequired = true;
    }
@endphp

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700']) }}>
    {{ $text ?? ($value ?? $slot) }}@if ($isRequired) <span class="font-bold text-red-600" title="Required">*</span>@endif
</label>
