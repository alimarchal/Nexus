@props([
    'name' => 'files[]',
    'accept' => null,
])

<div x-data="{
        files: [],
        dragging: false,
        addFiles(fileList) {
            this.files.forEach((file) => file.previewUrl && URL.revokeObjectURL(file.previewUrl));
            this.files = Array.from(fileList).map((file) => ({
                raw: file,
                name: file.name,
                size: file.size,
                isImage: file.type.startsWith('image/'),
                previewUrl: file.type.startsWith('image/') ? URL.createObjectURL(file) : null,
            }));
            this.$refs.input.files = fileList;
        },
        formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        },
    }" class="w-full">
    <div x-on:dragover.prevent="dragging = true" x-on:dragleave.prevent="dragging = false"
        x-on:drop.prevent="dragging = false; addFiles($event.dataTransfer.files)" x-on:click="$refs.input.click()"
        :class="dragging ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 scale-[1.01]' : 'border-gray-300 dark:border-gray-700'"
        class="cursor-pointer rounded-md border-2 border-dashed p-6 text-center transition-all duration-200 ease-out">
        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-8 w-8 text-gray-400 transition-transform duration-200"
            :class="dragging ? 'scale-125 text-indigo-500' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
            Drag &amp; drop files here, or <span class="font-semibold text-blue-600">browse</span>
        </p>
    </div>

    <template x-if="files.length">
        <ul class="mt-3 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <template x-for="file in files" :key="file.name + file.size">
                <li x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                    class="relative rounded-md border border-gray-200 dark:border-gray-700 p-2 text-center bg-gray-50 dark:bg-gray-900">
                    <template x-if="file.isImage">
                        <img :src="file.previewUrl" class="mx-auto h-16 w-16 object-cover rounded-md">
                    </template>
                    <template x-if="!file.isImage">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16 text-gray-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </template>
                    <p class="mt-1 truncate text-xs text-gray-600 dark:text-gray-300" x-text="file.name"></p>
                    <p class="text-[10px] text-gray-400" x-text="formatSize(file.size)"></p>
                </li>
            </template>
        </ul>
    </template>

    <input type="file" name="{{ $name }}" x-ref="input" multiple @if ($accept) accept="{{ $accept }}" @endif
        x-on:change="addFiles($event.target.files)" class="hidden">
</div>