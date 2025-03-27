<x-app-layout>
    @push('custom-scripts')
        <script src="https://cdn.tiny.cloud/1/izbyerk8x92uls8z2ulnezm5uaudhf41lw0lebop5ba724o5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    @endpush
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight inline-block">
            {{ __('HR Document / Notification') }}
        </h2>

    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
                    <x-validation-errors class="mb-4" />

                    @if (session('status'))
                        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ session('status') }}
                        </div>
                    @endif
                    <form class="w-full mx-auto" action="{{route('hrd.store')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="category_id" class="block mb-1">Category</label>
                            <select id="category_id" name="category_id" class="w-full py-2 px-3 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">None</option>
                                @foreach(\App\Models\Category::where('id','>=','3')->where('id','<=','8')->get() as $cat)
                                    <option value="{{$cat->id}}">{{$cat->name}}</option>
                                @endforeach

                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="title" class="block mb-1">Title</label>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   class="w-full py-2 px-3 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block mb-1">Description</label>
                            <textarea id="description"
                                      name="description"
                                      class="w-full py-2 px-3 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="file" class="block mb-1">Document</label>
                            <input type="file"
                                   id="file"
                                   name="file"
                                   class="w-full py-2 px-3 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="mt-6">
                            <button type="submit"
                                    class="max-w-lg py-2 px-4 bg-blue-500 hover:bg-blue-600 text-white rounded-md focus:outline-none">Submit</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
        @push('modals')
            <script>
                tinymce.init({
                    selector: 'textarea',
                    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
                });
            </script>
        @endpush
</x-app-layout>