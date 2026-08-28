@props([
    'name' => 'files[]',
    'accept' => null,
])

<div x-data="{
        files: [],
        dragging: false,
        addFiles(fileList) {
            this.files = Array.from(fileList);
            this.$refs.input.files = fileList;
        },
    }" class="w-full">
    <div x-on:dragover.prevent="dragging = true" x-on:dragleave.prevent="dragging = false"
        x-on:drop.prevent="dragging = false; addFiles($event.dataTransfer.files)" x-on:click="$refs.input.click()"
        :class="dragging ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-300 dark:border-gray-700'"
        class="cursor-pointer rounded-md border-2 border-dashed p-6 text-center transition-colors duration-150">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Drag &amp; drop files here, or <span class="font-semibold text-blue-600">browse</span>
        </p>
        <template x-if="files.length">
            <ul class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                <template x-for="file in files" :key="file.name">
                    <li x-text="file.name"></li>
                </template>
            </ul>
        </template>
    </div>

    <input type="file" name="{{ $name }}" x-ref="input" multiple @if ($accept) accept="{{ $accept }}" @endif
        x-on:change="files = Array.from($event.target.files)" class="hidden">
</div>