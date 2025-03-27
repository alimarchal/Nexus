<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight inline-block">
            {{ __('Human Resource Management Division') }}
        </h2>
        @can('Create HRD')
            <div class="flex justify-center items-center float-right">
                <a href="{{route('hrd.create')}}"
                   class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform dark:text-gray-200 dark:border-gray-200  dark:hover:bg-gray-700 ml-2"
                   title="Members List">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    <span class="hidden md:inline-block ml-2">Create HRD</span>
                </a>
            </div>
        @endcan

    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-12 gap-6 ">


                @foreach(\App\Models\Category::where('id','>','2')->where('id','<=','8')->get() as $cat)
                    <a href="{{ route('hrd.categoryId', $cat->id) }}"
                       class="transform bg-white hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        <div class="p-5">
                            <div class="grid grid-cols-3 gap-1">
                                <div class="col-span-2">
                                    <div class="text-3xl font-bold leading-8">
                                        @if($cat->id == 3)
                                            {{\App\Models\Hrd::where('category_id',3)->count()}}
                                        @elseif($cat->id == 4)
                                            {{\App\Models\Hrd::where('category_id',4)->count()}}
                                        @elseif($cat->id == 5)
                                            {{\App\Models\Hrd::where('category_id',5)->count()}}
                                        @elseif($cat->id == 6)
                                            {{\App\Models\Hrd::where('category_id',6)->count()}}
                                        @elseif($cat->id == 7)
                                            {{\App\Models\Hrd::where('category_id',7)->count()}}
                                        @elseif($cat->id == 8)
                                            {{\App\Models\Hrd::where('category_id',8)->count()}}
                                        @endif
                                    </div>
                                    <div class="mt-1 text-base font-bold text-gray-600">
                                        {{$cat->name}}
                                    </div>
                                </div>

                                <div class="col-span-1 flex items-center justify-end">
                                    @if($cat->id == 3)
                                        <img src="https://img.icons8.com/?size=128&id=S1RK2Nhn2FC5&format=png"
                                             alt="employees on leave" class="h-12 w-12">
                                    @elseif($cat->id == 4)
                                        <img src="https://img.icons8.com/?size=128&id=BX6vkAtyBpfy&format=png"
                                             alt="employees on leave" class="h-12 w-12">
                                    @elseif($cat->id == 5)
                                        <img src="https://img.icons8.com/?size=128&id=xgXIg96t1U9n&format=png"
                                             alt="employees on leave" class="h-12 w-12">
                                    @elseif($cat->id == 6)
                                        <img src="https://img.icons8.com/?size=128&id=RdkZXeIVnvq1&format=png"
                                             alt="employees on leave" class="h-12 w-12">
                                    @elseif($cat->id == 7)
                                        <img src="https://img.icons8.com/?size=128&id=KD2G07pkZ4yD&format=png"
                                             alt="employees on leave" class="h-12 w-12">
                                    @elseif($cat->id == 8)
                                        <img src="https://img.icons8.com/?size=128&id=14799&format=png"
                                             alt="employees on leave" class="h-12 w-12">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach


            </div>
        </div>
    </div>
</x-app-layout>