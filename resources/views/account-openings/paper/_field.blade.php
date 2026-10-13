{{-- One field of the old paper form, drawn from PaperFormSchema. Needs $field. --}}
@php
    $name = $field['name'];
    $id = 'paper_'.$name;
    $control = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm';
    $show = $field['show'] ?? [];
@endphp

<div @class(['md:col-span-2' => in_array($field['type'], ['textarea', 'repeater'], true)]) data-field="{{ $name }}"
    @if ($show !== []) x-show="holds(@js($show))" x-cloak @endif>
    @if ($field['type'] === 'checkbox')
        <label class="inline-flex items-center text-sm">
            <input type="hidden" name="data[{{ $name }}]" value="0">
            <input type="checkbox" name="data[{{ $name }}]" value="1" x-model="d.{{ $name }}" class="rounded border-gray-300">
            <span class="ml-2">{{ $field['label'] }}</span>
        </label>
    @else
        <x-label :for="$id" :value="$field['label'].(($field['required'] ?? false) ? ' *' : '')" />

        @switch($field['type'])
            @case('text')
            @case('secret')
                <x-input :id="$id" :name="'data['.$name.']'" type="text" autocomplete="off"
                    :maxlength="$field['max'] ?? 150" class="{{ $control }}" x-model="d.{{ $name }}" />
                @break

            @case('textarea')
                <textarea id="{{ $id }}" name="data[{{ $name }}]" rows="3" maxlength="{{ $field['max'] ?? 2000 }}" class="{{ $control }}" x-model="d.{{ $name }}"></textarea>
                @break

            @case('date')
                <input id="{{ $id }}" type="date" name="data[{{ $name }}]" class="{{ $control }}" x-model="d.{{ $name }}">
                @break

            @case('number')
                <input id="{{ $id }}" type="number" step="0.01" min="0" name="data[{{ $name }}]" class="{{ $control }}" x-model="d.{{ $name }}">
                @break

            @case('select')
                <select id="{{ $id }}" name="data[{{ $name }}]" class="{{ $control }}" x-model="d.{{ $name }}">
                    <option value="">-</option>
                    @foreach ($field['options'] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @break

            @case('radio')
                <div class="mt-1 flex flex-wrap gap-x-6 gap-y-2" role="radiogroup" aria-label="{{ $field['label'] }}">
                    @foreach ($field['options'] as $value => $label)
                        <label class="inline-flex items-center text-sm">
                            <input type="radio" name="data[{{ $name }}]" value="{{ $value }}" x-model="d.{{ $name }}" class="border-gray-300">
                            <span class="ml-2">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @break

            @case('checks')
                <div class="mt-1 flex flex-wrap gap-x-6 gap-y-2">
                    @foreach ($field['options'] as $value => $label)
                        <label class="inline-flex items-center text-sm">
                            <input type="checkbox" name="data[{{ $name }}][]" value="{{ $value }}" x-model="d.{{ $name }}" class="rounded border-gray-300">
                            <span class="ml-2">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @break

            @case('repeater')
                @php($rows = $values[$name] ?? [])
                <div class="mt-1 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-100 text-left">
                                <th class="px-2 py-1">S.No.</th>
                                @foreach ($field['columns'] as $column)
                                    <th class="px-2 py-1">{{ $column['label'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @for ($row = 0; $row < $field['rows']; $row++)
                                <tr class="border-b border-gray-100">
                                    <td class="px-2 py-1">{{ $row + 1 }}</td>
                                    @foreach ($field['columns'] as $column)
                                        <td class="px-2 py-1">
                                            <input type="text" name="data[{{ $name }}][{{ $row }}][{{ $column['name'] }}]" maxlength="{{ $column['max'] ?? 150 }}"
                                                value="{{ $rows[$row][$column['name']] ?? '' }}" aria-label="{{ $column['label'] }} {{ $row + 1 }}"
                                                class="block w-full rounded-md border-gray-300 text-sm shadow-sm">
                                        </td>
                                    @endforeach
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
                @break
        @endswitch

        @if (isset($field['other']))
            <div class="mt-2" x-show="picked('{{ $name }}', @js((array) $field['other']))" x-cloak>
                <x-input :name="'data['.$name.'_other]'" type="text" maxlength="150" placeholder="Other (specify)" aria-label="Other (specify)"
                    class="{{ $control }}" x-model="d.{{ $name }}_other" />
                <x-input-error :for="'data.'.$name.'_other'" class="mt-1" />
            </div>
        @endif

        @if (! empty($field['help']))
            <p class="mt-1 text-xs text-gray-500">{{ $field['help'] }}</p>
        @endif
        <x-input-error :for="'data.'.$name" class="mt-1" />
    @endif
</div>
