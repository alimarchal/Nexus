<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            AKSIC Application Details - {{ $aksicApplication->application_no }}
        </h2>

        <div class="flex justify-center items-center float-right">
            <a href="{{ route('aksic-applications.index') }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-blue-950 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 focus:bg-green-800 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <!-- Arrow Left Icon SVG -->
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
            <a href="{{ route('aksic-applications.pdf', $aksicApplication) }}"
                class="inline-flex items-center ml-2 px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Download PDF
            </a>
        </div>
    </x-slot>

    <!-- Custom CSS for bordered tables -->
    <style>
        .bordered-table {
            border-collapse: collapse;
            width: 100%;
        }

        .bordered-table th,
        .bordered-table td {
            border: 1px solid #d1d5db;
            padding: 12px;
            text-align: left;
        }

        .bordered-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
        }

        .bordered-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .bordered-table tr:hover {
            background-color: #f3f4f6;
        }

        .image-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
        }

        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            max-height: 80%;
            margin-top: 5%;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Main Application Details Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- Personal Information Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">Personal
                            Information</h3>
                        <table class="bordered-table">
                            <tr>
                                <th style="width: 200px;">Application No</th>
                                <td>{{ $aksicApplication->application_no }}</td>
                                <th style="width: 200px;">Applicant ID</th>
                                <td>{{ $aksicApplication->applicant_id }}</td>
                            </tr>
                            <tr>
                                <th>Full Name</th>
                                <td>{{ $aksicApplication->name }}</td>
                                <th>Father's Name</th>
                                <td>{{ $aksicApplication->fatherName }}</td>
                            </tr>
                            <tr>
                                <th>CNIC</th>
                                <td>{{ $aksicApplication->cnic }}</td>
                                <th>CNIC Issue Date</th>
                                <td>{{ $aksicApplication->cnic_issue_date ?
                                    $aksicApplication->cnic_issue_date->format('d-M-Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Date of Birth</th>
                                <td>{{ $aksicApplication->dob ? $aksicApplication->dob->format('d-M-Y') : 'N/A' }}</td>
                                <th>Phone</th>
                                <td>{{ $aksicApplication->phone }}</td>
                            </tr>
                            <tr>
                                <th>Permanent Address</th>
                                <td colspan="3">{{ $aksicApplication->permanentAddress }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Business Information Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">Business
                            Information</h3>
                        <table class="bordered-table">
                            <tr>
                                <th style="width: 200px;">Business Name</th>
                                <td>{{ $aksicApplication->businessName }}</td>
                                <th style="width: 200px;">Business Type</th>
                                <td>{{ $aksicApplication->businessType }}</td>
                            </tr>
                            <tr>
                                <th>Business Address</th>
                                <td colspan="3">{{ $aksicApplication->businessAddress }}</td>
                            </tr>
                            <tr>
                                <th>Business Category ID</th>
                                <td>{{ $aksicApplication->business_category_id }}</td>
                                <th>Sub Category ID</th>
                                <td>{{ $aksicApplication->business_sub_category_id }}</td>
                            </tr>
                            <tr>
                                <th>Quota</th>
                                <td>{{ $aksicApplication->quota }}</td>
                                <th>Tier</th>
                                <td>{{ $aksicApplication->tier }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Financial Details Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">Financial
                            Details</h3>
                        <table class="bordered-table">
                            <tr>
                                <th style="width: 200px;">Amount</th>
                                <td>Rs. {{ number_format($aksicApplication->amount, 2) }}</td>
                                <th style="width: 200px;">Challan Fee</th>
                                <td>Rs. {{ number_format($aksicApplication->challan_fee, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Fee Status</th>
                                <td>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $aksicApplication->fee_status === 'Paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $aksicApplication->fee_status }}
                                    </span>
                                </td>
                                <th>Fee Branch Code</th>
                                <td>{{ $aksicApplication->fee_branch_code }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Application Details Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">
                            Application Details</h3>
                        <table class="bordered-table">
                            <tr>
                                <th style="width: 200px;">Status</th>
                                <td>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $aksicApplication->status === 'Approved' ? 'bg-green-100 text-green-800' : 
                                       ($aksicApplication->status === 'In Progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $aksicApplication->status }}
                                    </span>
                                </td>
                                <th style="width: 200px;">Bank Status</th>
                                <td>{{ $aksicApplication->bank_status }}</td>
                            </tr>
                            <tr>
                                <th>District</th>
                                <td>{{ $aksicApplication->district_name }}</td>
                                <th>Tehsil</th>
                                <td>{{ $aksicApplication->tehsil_name }}</td>
                            </tr>
                            <tr>
                                <th>District ID</th>
                                <td>{{ $aksicApplication->district_id }}</td>
                                <th>Tehsil ID</th>
                                <td>{{ $aksicApplication->tehsil_id }}</td>
                            </tr>
                            <tr>
                                <th>Branch ID</th>
                                <td>{{ $aksicApplication->branch_id }}</td>
                                <th>Chosen Branch ID</th>
                                <td>{{ $aksicApplication->applicant_choosed_branch_id }}</td>
                            </tr>
                            <tr>
                                <th>Challan Branch ID</th>
                                <td>{{ $aksicApplication->challan_branch_id }}</td>
                                <th>Created At</th>
                                <td>{{ $aksicApplication->created_at->format('d-M-Y H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Education Details Section -->
                    @if($aksicApplication->educations->count() > 0)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">Education
                            Details</h3>
                        <table class="bordered-table">
                            <thead>
                                <tr>
                                    <th>Education Level</th>
                                    <th>Degree Title</th>
                                    <th>Institute</th>
                                    <th>Passing Year</th>
                                    <th>Grade/Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($aksicApplication->educations as $education)
                                <tr>
                                    <td>{{ $education->education_level }}</td>
                                    <td>{{ $education->degree_title }}</td>
                                    <td>{{ $education->institute }}</td>
                                    <td>{{ $education->passing_year }}</td>
                                    <td>{{ $education->grade_or_percentage }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <!-- Status History Section -->
                    @if($aksicApplication->statusLogs->count() > 0)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">Status
                            History</h3>
                        <table class="bordered-table">
                            <thead>
                                <tr>
                                    <th>Old Status</th>
                                    <th>New Status</th>
                                    <th>Changed By Type</th>
                                    <th>Changed By ID</th>
                                    <th>Remarks</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($aksicApplication->statusLogs as $log)
                                <tr>
                                    <td>{{ $log->old_status }}</td>
                                    <td>{{ $log->new_status }}</td>
                                    <td>{{ $log->changed_by_type }}</td>
                                    <td>{{ $log->changed_by_id }}</td>
                                    <td>{{ $log->remarks }}</td>
                                    <td>{{ $log->created_at->format('d-M-Y H:i:s') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <!-- Document Images Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">Document
                            Images</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Challan Image -->
                            <div class="text-center">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Challan Image</h4>
                                @if($aksicApplication->challan_image_url)
                                <div class="border border-gray-300 rounded-lg p-2 bg-gray-50">
                                    <img src="{{ $aksicApplication->challan_image_url }}" alt="Challan Image"
                                        class="max-w-full h-32 object-cover mx-auto cursor-pointer rounded"
                                        onclick="openImageModal('{{ $aksicApplication->challan_image_url }}', 'Challan Image')">
                                    <div class="mt-2">
                                        <button
                                            onclick="downloadImage('{{ $aksicApplication->challan_image_url }}', 'challan_{{ $aksicApplication->application_no }}')"
                                            class="text-blue-600 hover:text-blue-800 text-sm">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                            Download
                                        </button>
                                    </div>
                                </div>
                                @else
                                <div class="border border-gray-300 rounded-lg p-8 bg-gray-50 text-gray-500">
                                    No image available
                                </div>
                                @endif
                            </div>

                            <!-- CNIC Front -->
                            <div class="text-center">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">CNIC Front</h4>
                                @if($aksicApplication->cnic_front_url)
                                <div class="border border-gray-300 rounded-lg p-2 bg-gray-50">
                                    <img src="{{ $aksicApplication->cnic_front_url }}" alt="CNIC Front"
                                        class="max-w-full h-32 object-cover mx-auto cursor-pointer rounded"
                                        onclick="openImageModal('{{ $aksicApplication->cnic_front_url }}', 'CNIC Front')">
                                    <div class="mt-2">
                                        <button
                                            onclick="downloadImage('{{ $aksicApplication->cnic_front_url }}', 'cnic_front_{{ $aksicApplication->application_no }}')"
                                            class="text-blue-600 hover:text-blue-800 text-sm">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                            Download
                                        </button>
                                    </div>
                                </div>
                                @else
                                <div class="border border-gray-300 rounded-lg p-8 bg-gray-50 text-gray-500">
                                    No image available
                                </div>
                                @endif
                            </div>

                            <!-- CNIC Back -->
                            <div class="text-center">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">CNIC Back</h4>
                                @if($aksicApplication->cnic_back_url)
                                <div class="border border-gray-300 rounded-lg p-2 bg-gray-50">
                                    <img src="{{ $aksicApplication->cnic_back_url }}" alt="CNIC Back"
                                        class="max-w-full h-32 object-cover mx-auto cursor-pointer rounded"
                                        onclick="openImageModal('{{ $aksicApplication->cnic_back_url }}', 'CNIC Back')">
                                    <div class="mt-2">
                                        <button
                                            onclick="downloadImage('{{ $aksicApplication->cnic_back_url }}', 'cnic_back_{{ $aksicApplication->application_no }}')"
                                            class="text-blue-600 hover:text-blue-800 text-sm">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                            Download
                                        </button>
                                    </div>
                                </div>
                                @else
                                <div class="border border-gray-300 rounded-lg p-8 bg-gray-50 text-gray-500">
                                    No image available
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- API Response Data (for debugging/reference) -->
                    @if($aksicApplication->api_call_json && is_array($aksicApplication->api_call_json))
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b pb-2">API
                                Response Data</h3>
                            <button onclick="toggleApiData()" class="text-sm text-blue-600 hover:text-blue-800">
                                <span id="apiToggleText">Show</span> Raw Data
                            </button>
                        </div>
                        <div id="apiDataContent" class="hidden">
                            <div class="bg-gray-100 p-4 rounded-lg overflow-auto max-h-96">
                                <pre
                                    class="text-sm text-gray-800">{{ json_encode($aksicApplication->api_call_json, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="image-modal">
        <span class="close" onclick="closeImageModal()">&times;</span>
        <img class="modal-content" id="modalImage">
        <div id="caption" class="text-center text-white mt-4"></div>
    </div>

    @push('scripts')
    <script>
        // Image Modal Functions
        function openImageModal(src, caption) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            const captionText = document.getElementById('caption');
            
            modal.style.display = 'block';
            modalImg.src = src;
            captionText.innerHTML = caption;
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Download Image Function
        function downloadImage(url, filename) {
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Toggle API Data
        function toggleApiData() {
            const content = document.getElementById('apiDataContent');
            const toggleText = document.getElementById('apiToggleText');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                toggleText.textContent = 'Hide';
            } else {
                content.classList.add('hidden');
                toggleText.textContent = 'Show';
            }
        }

        // Close modal when clicking outside the image
        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target === modal) {
                closeImageModal();
            }
        }
    </script>
    @endpush
</x-app-layout>