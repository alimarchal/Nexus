{{-- The saved old paper form, section by section, for the request page and the print. Needs $values. --}}
@php
    $display = function (array $field, mixed $value) use ($values): string {
        if ($value === null || $value === '' || $value === []) {
            return '—';
        }

        $options = $field['options'] ?? [];
        $text = match ($field['type']) {
            'checkbox' => $value ? 'Yes' : 'No',
            'checks' => implode(', ', array_map(fn ($key) => $options[$key] ?? $key, (array) $value)),
            'radio', 'select' => $options[$value] ?? $value,
            'secret' => str_repeat('•', min(mb_strlen((string) $value), 12)),
            'number' => number_format((float) $value, 2),
            default => (string) $value,
        };

        $other = $values[$field['name'].'_other'] ?? null;

        return $other ? $text.' — '.$other : $text;
    };
@endphp

@foreach (\App\Support\PaperForm\PaperFormSchema::visibleSections($values) as $section)
    <section class="paper-section mb-6" data-section="{{ $section['key'] }}">
        <h3 class="mb-3 text-base font-bold uppercase tracking-wide text-green-800">{{ $section['number'] }}. {{ $section['title'] }}</h3>
        <dl class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
            @foreach ($section['fields'] as $field)
                @if ($field['type'] === 'repeater')
                    <div class="md:col-span-2">
                        <dt class="text-gray-500">{{ $field['label'] }}</dt>
                        <dd>
                            <table class="mt-1 w-full text-xs">
                                <thead><tr class="bg-gray-100 text-left"><th class="px-2 py-1">S.No.</th>@foreach ($field['columns'] as $column)<th class="px-2 py-1">{{ $column['label'] }}</th>@endforeach</tr></thead>
                                <tbody>
                                    @forelse ($values[$field['name']] ?? [] as $row)
                                        <tr class="border-b border-gray-100"><td class="px-2 py-1">{{ $loop->iteration }}</td>@foreach ($field['columns'] as $column)<td class="px-2 py-1">{{ $row[$column['name']] ?? '—' }}</td>@endforeach</tr>
                                    @empty
                                        <tr><td class="px-2 py-1 text-gray-400" colspan="{{ count($field['columns']) + 1 }}">—</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </dd>
                    </div>
                @else
                    <div @class(['md:col-span-2' => $field['type'] === 'textarea'])>
                        <dt class="text-gray-500">{{ $field['label'] }}</dt>
                        <dd class="font-semibold" data-value="{{ $field['name'] }}">{{ $display($field, $values[$field['name']] ?? null) }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>
    </section>
@endforeach
