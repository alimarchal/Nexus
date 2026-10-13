{{-- The saved old paper form, section by section, in the layout of the paper form. Needs $values; $printing for the print. --}}
@php
    // Options are listed the way the paper form prints them, with the chosen ones ticked.
    $ticks = function (array $field, mixed $value) use ($values): \Illuminate\Support\HtmlString {
        $chosen = array_map('strval', (array) $value);
        $other = $values[$field['name'].'_other'] ?? null;
        $html = '';

        foreach ($field['options'] as $key => $label) {
            $on = in_array((string) $key, $chosen, true);
            $html .= '<span class="mr-4 inline-block whitespace-nowrap">'.($on ? '&#9745;' : '&#9744;').' '.e($label)
                .($on && $other && in_array($key, (array) ($field['other'] ?? []), true) ? ': <u>'.e($other).'</u>' : '').'</span> ';
        }

        return new \Illuminate\Support\HtmlString($html);
    };

    $plain = function (array $field, mixed $value): string {
        if ($value === null || $value === '' || $value === []) {
            return '—';
        }

        return match ($field['type']) {
            'secret' => str_repeat('•', min(mb_strlen((string) $value), 12)),
            'number' => ($field['integer'] ?? false) ? (string) $value : number_format((float) $value, 2),
            'date' => \Illuminate\Support\Carbon::parse($value)->format('d.m.Y'),
            default => (string) $value,
        };
    };
@endphp

@foreach (\App\Support\PaperForm\PaperFormSchema::visibleSections($values) as $section)
    <section class="paper-section mb-6" data-section="{{ $section['key'] }}">
        <h3 class="mb-3 text-base font-bold uppercase tracking-wide text-green-800">{{ $section['number'] }}. {{ $section['title'] }}</h3>
        <dl class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
            @foreach ($section['fields'] as $field)
                @if ($field['type'] === 'heading')
                    <div class="md:col-span-2"><dt class="font-bold text-gray-800">{{ rtrim($field['label'], ' *') }}</dt></div>
                @elseif ($field['type'] === 'terms')
                    @if ($printing ?? false)
                        <div class="md:col-span-2">@include('account-openings.paper._terms', ['printing' => true])</div>
                    @endif
                @elseif ($field['type'] === 'repeater')
                    <div class="md:col-span-2">
                        <dt class="text-gray-500">{{ $field['label'] }}</dt>
                        <dd>
                            <table class="mt-1 w-full text-xs">
                                <thead><tr class="bg-gray-100 text-left"><th class="px-2 py-1">S.No.</th>@foreach ($field['columns'] as $column)<th class="px-2 py-1">{{ $column['label'] }}</th>@endforeach</tr></thead>
                                <tbody>
                                    @forelse ($values[$field['name']] ?? [] as $row)
                                        <tr class="border-b border-gray-100"><td class="px-2 py-1">{{ $loop->iteration }}</td>@foreach ($field['columns'] as $column)<td class="px-2 py-1">@if (($column['type'] ?? 'text') === 'checkbox'){{ ($row[$column['name']] ?? false) ? '☑' : '☐' }}@else{{ $row[$column['name']] ?? '—' }}@endif</td>@endforeach</tr>
                                    @empty
                                        <tr><td class="px-2 py-1 text-gray-400" colspan="{{ count($field['columns']) + 1 }}">—</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </dd>
                    </div>
                @elseif (in_array($field['type'], ['radio', 'select', 'checks'], true))
                    <div class="md:col-span-2" data-value="{{ $field['name'] }}">
                        <dt class="text-gray-500">{{ $field['label'] }}</dt>
                        <dd class="font-semibold">{{ $ticks($field, $values[$field['name']] ?? null) }}</dd>
                    </div>
                @elseif ($field['type'] === 'checkbox')
                    <div class="md:col-span-2" data-value="{{ $field['name'] }}">
                        <dd class="font-semibold">{{ ($values[$field['name']] ?? false) ? '☑' : '☐' }} {{ $field['label'] }}</dd>
                    </div>
                @else
                    <div @class(['md:col-span-2' => $field['type'] === 'textarea'])>
                        <dt class="text-gray-500">{{ $field['label'] }}</dt>
                        <dd class="font-semibold" data-value="{{ $field['name'] }}">{{ $plain($field, $values[$field['name']] ?? null) }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>
    </section>
@endforeach
