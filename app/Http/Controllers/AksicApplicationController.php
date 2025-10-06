<?php

namespace App\Http\Controllers;

use App\Helpers\FileStorageHelper;
use App\Http\Requests\StoreAksicApplicationRequest;
use App\Http\Requests\UpdateAksicApplicationRequest;
use App\Models\AksicApplication;
use App\Models\AksicApplicationEducation;
use App\Models\AksicApplicationStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Carbon\Carbon;

class AksicApplicationController extends Controller
{
    /**
     * Display a listing of AKSIC applications with filtering capabilities
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Build query with filters using Spatie QueryBuilder
        $applications = QueryBuilder::for(AksicApplication::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),                    // Filter by status
                AllowedFilter::exact('fee_status'),               // Filter by fee status
                AllowedFilter::partial('name'),                   // Search by name
                AllowedFilter::partial('cnic'),                   // Search by CNIC
                AllowedFilter::partial('application_no'),         // Search by application number
                AllowedFilter::partial('businessName'),           // Search by business name
                AllowedFilter::partial('businessType'),           // Search by business type
                AllowedFilter::partial('district_name'),          // Filter by district
                AllowedFilter::partial('tehsil_name'),           // Filter by tehsil
                AllowedFilter::exact('tier'),                     // Filter by tier
                AllowedFilter::callback('date_from', function ($query, $value) {
                    $query->whereDate('created_at', '>=', $value);
                }),
                AllowedFilter::callback('date_to', function ($query, $value) {
                    $query->whereDate('created_at', '<=', $value);
                }),
                AllowedFilter::callback('amount_min', function ($query, $value) {
                    $query->where('amount', '>=', $value);
                }),
                AllowedFilter::callback('amount_max', function ($query, $value) {
                    $query->where('amount', '<=', $value);
                })
            ])
            ->with(['educations', 'statusLogs', 'businessCategory', 'businessSubCategory'])              // Eager load relationships
            ->oldest()                                        // Order by newest first
            ->paginate(10);                                   // Paginate results

        return view('aksic-applications.index', compact('applications'));
    }



    /**
     * Update single application status back to the API
     */
    private function updateSingleApplicationStatus($applicationId)
    {
        try {
            Log::info('Updating application status on API', ['application_id' => $applicationId]);

            // Send status update to API for single application
            $response = Http::withToken(config('app.aksic_api_token'))
                ->withOptions([
                    'verify' => false, // Disable SSL verification
                    'timeout' => 30,
                ])
                ->post('https://sic.ajk.gov.pk/pmylp/api/bank/applications/status-update', [
                    'applications' => [
                        [
                            'id' => $applicationId,
                            'status' => 'In Progress',
                            'remarks' => 'BANK AJK System Automatically Collected This Application'
                        ]
                    ]
                ]);

            if (!$response->successful()) {
                Log::error('Failed to update application status on API', [
                    'application_id' => $applicationId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                throw new \Exception("Failed to update status on API for application {$applicationId}: " . $response->body());
            }

            Log::info('Successfully updated application status on API', [
                'application_id' => $applicationId,
                'response' => $response->json()
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating application status on API', [
                'application_id' => $applicationId,
                'error' => $e->getMessage()
            ]);
            throw $e; // Re-throw to trigger transaction rollback
        }
    }

    /**
     * Update application statuses back to the API in batch
     */
    private function updateApplicationsStatusBatch($applicationIds)
    {
        try {
            Log::info('Starting batch update of application statuses on API', [
                'application_ids' => $applicationIds,
                'count' => count($applicationIds)
            ]);

            // Prepare the status update payload
            $statusUpdatePayload = [];
            foreach ($applicationIds as $applicationId) {
                $statusUpdatePayload[] = [
                    'id' => $applicationId,
                    'status' => 'In Progress',
                    'remarks' => 'BANK AJK System Automatically Collected This Application'
                ];
            }

            // Send batch status update to API
            $response = Http::withToken(config('app.aksic_api_token'))
                ->withOptions([
                    'verify' => false, // Disable SSL verification
                    'timeout' => 30,
                ])
                ->post('https://sic.ajk.gov.pk/pmylp/api/bank/applications/status-update', [
                    'applications' => $statusUpdatePayload
                ]);

            if ($response->successful()) {
                Log::info('Successfully updated application statuses on API (batch)', [
                    'application_ids' => $applicationIds,
                    'count' => count($statusUpdatePayload),
                    'response' => $response->json()
                ]);
            } else {
                Log::error('Failed to update application statuses on API (batch)', [
                    'application_ids' => $applicationIds,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error updating application statuses on API (batch)', [
                'application_ids' => $applicationIds,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync applications from external API
     */
    public function syncApplications()
    {
        try {
            Log::info('Starting AKSIC applications sync from API');

            // Configuration flag to control image downloading
            $downloadImages = config('app.aksic_download_images', true); // Default to true

            Log::info('Image download setting', ['download_images' => $downloadImages]);

            // Call the external API with SSL verification disabled for development
            $response = Http::withToken(config('app.aksic_api_token'))
                ->withOptions([
                    'verify' => false, // Disable SSL verification
                    'timeout' => 30,
                ])
                ->get('https://sic.ajk.gov.pk/pmylp/api/bank/applications');

            if (!$response->successful()) {
                Log::error('API call failed', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch applications from API. Status: ' . $response->status()
                ]);
            }

            $apiData = $response->json();

            if (!$apiData['success'] || !isset($apiData['data']['count']) || $apiData['data']['count'] < 1) {
                Log::info('No applications found in API response');
                return response()->json([
                    'success' => false,
                    'message' => 'No applications found in the API response.'
                ]);
            }

            $applications = $apiData['data']['applications'];
            $syncResults = [
                'total' => count($applications),
                'updated' => 0,
                'created' => 0,
                'failed' => 0,
                'errors' => []
            ];

            // Track successfully processed applications for batch status update
            $successfullyProcessedIds = [];

            // Process each application in a transaction
            foreach ($applications as $appData) {
                try {
                    DB::beginTransaction();

                    // Download and store images locally (based on configuration flag)
                    $localChallanImage = null;
                    $localCnicFront = null;
                    $localCnicBack = null;

                    if ($downloadImages) {
                        $folderName = 'aksic-applications/' . $appData['application_no'];

                        // Download challan image
                        if (!empty($appData['challan_image_url'])) {
                            try {
                                $localChallanImage = $this->downloadAndStoreImage(
                                    $appData['challan_image_url'],
                                    $appData['challan_image'],
                                    $folderName
                                );
                            } catch (\Exception $e) {
                                Log::warning('Failed to download challan image', [
                                    'applicant_id' => $appData['id'],
                                    'url' => $appData['challan_image_url'],
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }

                        // Download CNIC front image
                        if (!empty($appData['cnic_front_url'])) {
                            try {
                                $localCnicFront = $this->downloadAndStoreImage(
                                    $appData['cnic_front_url'],
                                    $appData['cnic_front'],
                                    $folderName
                                );
                            } catch (\Exception $e) {
                                Log::warning('Failed to download CNIC front image', [
                                    'applicant_id' => $appData['id'],
                                    'url' => $appData['cnic_front_url'],
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }

                        // Download CNIC back image
                        if (!empty($appData['cnic_back_url'])) {
                            try {
                                $localCnicBack = $this->downloadAndStoreImage(
                                    $appData['cnic_back_url'],
                                    $appData['cnic_back'],
                                    $folderName
                                );
                            } catch (\Exception $e) {
                                Log::warning('Failed to download CNIC back image', [
                                    'applicant_id' => $appData['id'],
                                    'url' => $appData['cnic_back_url'],
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }

                    // Map API data to our database structure
                    $applicationData = [
                        'applicant_id' => $appData['id'],
                        'name' => $appData['name'],
                        'fatherName' => $appData['fatherName'],
                        'cnic' => $appData['cnic'],
                        'application_no' => $appData['application_no'],
                        'cnic_issue_date' => $appData['cnic_issue_date'] ? Carbon::parse($appData['cnic_issue_date']) : null,
                        'dob' => $appData['dob'] ? Carbon::parse($appData['dob']) : null,
                        'phone' => $appData['phone'],
                        'businessName' => $appData['businessName'],
                        'businessType' => $appData['businessType'],
                        'quota' => $appData['quota'],
                        'businessAddress' => $appData['businessAddress'],
                        'permanentAddress' => $appData['permanentAddress'],
                        'business_category_id' => $appData['business_category_id'],
                        'business_sub_category_id' => $appData['business_sub_category_id'],
                        'tier' => $appData['tier'],
                        'amount' => $appData['amount'],
                        'district_id' => $appData['district_id'],
                        'tehsil_id' => $appData['tehsil_id'],
                        'applicant_choosed_branch_id' => $appData['applicant_choosed_branch'],
                        'branch_id' => $appData['branch_id'],
                        'challan_branch_id' => $appData['challan_branch_id'],
                        'challan_fee' => $appData['challan_fee'],
                        // Store local file paths if downloaded, otherwise original filenames from API
                        'challan_image' => $downloadImages ? ($localChallanImage ?? $appData['challan_image']) : $appData['challan_image'],
                        'cnic_front' => $downloadImages ? ($localCnicFront ?? $appData['cnic_front']) : $appData['cnic_front'],
                        'cnic_back' => $downloadImages ? ($localCnicBack ?? $appData['cnic_back']) : $appData['cnic_back'],
                        // Store original API URLs for reference
                        'challan_image_url' => $appData['challan_image_url'] ?? null,
                        'cnic_front_url' => $appData['cnic_front_url'] ?? null,
                        'cnic_back_url' => $appData['cnic_back_url'] ?? null,
                        'fee_status' => $appData['fee_status'],
                        'status' => $appData['status'],
                        'bank_status' => $appData['bank_status'],
                        'fee_branch_code' => $appData['fee_branch']['branch_code'] ?? null,
                        'district_name' => $appData['district']['name'] ?? null,
                        'tehsil_name' => $appData['tehsil']['name'] ?? null,
                        'applicant_choosed_branch_code' => $appData['applicant_choosed_branch_code'],
                        'challan_branch_code' => $appData['challan_branch_code'],
                        'api_call_json' => $appData, // Store entire API response including URLs
                    ];

                    // Check if application exists
                    $existingApp = AksicApplication::where('applicant_id', $appData['id'])->first();

                    if ($existingApp) {
                        // Update existing application
                        $existingApp->update($applicationData);
                        $application = $existingApp;
                        $syncResults['updated']++;
                        Log::info('Updated application', ['applicant_id' => $appData['id']]);
                    } else {
                        // Create new application
                        $application = AksicApplication::create($applicationData);
                        $syncResults['created']++;
                        Log::info('Created new application', ['applicant_id' => $appData['id']]);
                    }

                    // Clear existing educations and status logs
                    AksicApplicationEducation::where('applicant_id', $appData['id'])->delete();
                    AksicApplicationStatusLog::where('applicant_id', $appData['id'])->delete();

                    // Insert educations
                    if (isset($appData['educations']) && is_array($appData['educations'])) {
                        foreach ($appData['educations'] as $education) {
                            AksicApplicationEducation::create([
                                'aksic_application_id' => $application->id,
                                'applicant_id' => $appData['id'],
                                'education_level' => $education['education_level'],
                                'degree_title' => $education['degree_title'],
                                'institute' => $education['institute'],
                                'passing_year' => $education['passing_year'],
                                'grade_or_percentage' => $education['grade_or_percentage'],
                                'educations_json' => $education,
                            ]);
                        }
                    }

                    // Insert status logs
                    if (isset($appData['status_logs']) && is_array($appData['status_logs'])) {
                        foreach ($appData['status_logs'] as $statusLog) {
                            AksicApplicationStatusLog::create([
                                'aksic_application_id' => $application->id,
                                'applicant_id' => $appData['id'],
                                'old_status' => $statusLog['old_status'],
                                'new_status' => $statusLog['new_status'],
                                'changed_by_type' => $statusLog['changed_by_type'],
                                'changed_by_id' => $statusLog['changed_by_id'],
                                'remarks' => $statusLog['remarks'],
                                'status_json' => $statusLog,
                            ]);
                        }
                    }

                    DB::commit();

                    // Add to successful applications list for batch status update
                    $successfullyProcessedIds[] = $appData['id'];

                } catch (\Exception $e) {
                    DB::rollBack();
                    $syncResults['failed']++;
                    $syncResults['errors'][] = "Application ID {$appData['id']}: " . $e->getMessage();
                    Log::error('Failed to sync application', [
                        'applicant_id' => $appData['id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('AKSIC applications sync completed', $syncResults);

            // Update status of all successfully processed applications in one batch call
            if (!empty($successfullyProcessedIds)) {
                $this->updateApplicationsStatusBatch($successfullyProcessedIds);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sync completed successfully!',
                'results' => $syncResults
            ]);

        } catch (\Exception $e) {
            Log::error('AKSIC sync failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAksicApplicationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AksicApplication $aksicApplication)
    {
        // Load relationships for detailed view
        $aksicApplication->load(['educations', 'statusLogs', 'businessCategory', 'businessSubCategory']);

        return view('aksic-applications.show', compact('aksicApplication'));
    }

    /**
     * Generate PDF for the specified application
     */
    public function downloadPdf(AksicApplication $aksicApplication)
    {
        // Load relationships for PDF generation
        $aksicApplication->load(['educations', 'statusLogs']);

        // Return JSON data for PDF generation
        return response()->json([
            'success' => true,
            'application' => $aksicApplication,
            'exported_at' => now()->toISOString()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AksicApplication $aksicApplication)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAksicApplicationRequest $request, AksicApplication $aksicApplication)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AksicApplication $aksicApplication)
    {
        //
    }

    /**
     * Update status of a specific AKSIC application
     */
    public function updateStatus(Request $request, AksicApplication $aksicApplication)
    {
        try {
            $request->validate([
                'old_status' => 'required|string',
                'new_status' => 'required|string',
                'remarks' => 'nullable|string',
                'applicant_id' => 'required|integer'
            ]);

            Log::info('Starting status update', [
                'application_id' => $aksicApplication->id,
                'applicant_id' => $request->applicant_id,
                'old_status' => $request->old_status,
                'new_status' => $request->new_status,
                'user_id' => auth()->id()
            ]);

            DB::beginTransaction();

            // First, call the external API to update status
            $apiResponse = null;
            try {
                $apiToken = config('app.aksic_api_token');
                if (!$apiToken) {
                    throw new \Exception('AKSIC API token not configured. Please set AKSIC_API_TOKEN in your environment file.');
                }

                Log::info('Calling external API for status update', [
                    'applicant_id' => $request->applicant_id,
                    'status' => $request->new_status,
                    'remarks' => $request->remarks
                ]);

                $response = Http::withToken($apiToken)
                    ->withOptions([
                        'verify' => false, // Disable SSL verification
                        'timeout' => 30,
                    ])
                    ->asForm() // Use form data instead of JSON as shown in Postman
                    ->post("https://sic.ajk.gov.pk/pmylp/api/bank/applications/{$request->applicant_id}/status-update", [
                        'status' => $request->new_status,
                        'remarks' => $request->remarks
                    ]);

                if ($response->successful()) {
                    $apiResponse = $response->json();
                    Log::info('External API call successful', [
                        'applicant_id' => $request->applicant_id,
                        'api_response' => $apiResponse
                    ]);
                } else {
                    Log::error('External API call failed', [
                        'applicant_id' => $request->applicant_id,
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    throw new \Exception('Failed to update status via external API: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('External API call exception', [
                    'applicant_id' => $request->applicant_id,
                    'error' => $e->getMessage()
                ]);
                throw new \Exception('External API error: ' . $e->getMessage());
            }

            // If API call successful, update local database
            $updateData = [
                'bank_status' => $request->new_status, // Update bank_status as this is what we're managing from bank side
                'updated_at' => now()
            ];

            // Define legacy statuses that should update the main status field
            $legacyStatuses = ['Pending', 'Approved', 'Forwarded', 'Rejected'];

            // If it's a legacy status, update the main status field
            if (in_array($request->new_status, $legacyStatuses)) {
                $updateData['status'] = $request->new_status;
                Log::info('Legacy status detected, updating main status field', [
                    'new_status' => $request->new_status
                ]);
            }

            // Also update the main status if the API response includes it
            if (isset($apiResponse['data']['status'])) {
                $updateData['status'] = $apiResponse['data']['status'];
            }

            Log::info('Updating local application data', [
                'application_id' => $aksicApplication->id,
                'update_data' => $updateData
            ]);

            $aksicApplication->update($updateData);

            // Create status log entry
            AksicApplicationStatusLog::create([
                'aksic_application_id' => $aksicApplication->id,
                'applicant_id' => $aksicApplication->applicant_id,
                'old_status' => $request->old_status,
                'new_status' => $request->new_status,
                'changed_by_type' => 'User',
                'changed_by_id' => auth()->id(),
                'remarks' => $request->remarks,
                'status_json' => [
                    'api_response' => $apiResponse,
                    'changed_at' => now()->toISOString(),
                    'changed_by_user' => auth()->user()->name ?? 'System',
                ],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            Log::info('Status updated successfully', [
                'application_id' => $aksicApplication->id,
                'applicant_id' => $aksicApplication->applicant_id,
                'old_status' => $request->old_status,
                'new_status' => $request->new_status,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'application' => $aksicApplication->fresh(['statusLogs']),
                'api_response' => $apiResponse
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update status', [
                'application_id' => $aksicApplication->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download and store image from URL using FileStorageHelper
     */
    private function downloadAndStoreImage(string $imageUrl, string $originalFilename, string $folderName): ?string
    {
        try {
            // Get image content from URL with SSL verification disabled
            $imageContent = file_get_contents($imageUrl, false, stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
                'http' => [
                    'timeout' => 30,
                ]
            ]));

            if ($imageContent === false) {
                throw new \Exception("Failed to download image from URL: {$imageUrl}");
            }

            // Get file extension from original filename or URL
            $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
            if (empty($extension)) {
                // Try to get extension from URL
                $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            }
            if (empty($extension)) {
                $extension = 'jpg'; // Default extension
            }

            // Create a temporary file from the downloaded content
            $tempFilePath = tempnam(sys_get_temp_dir(), 'aksic_image_');
            file_put_contents($tempFilePath, $imageContent);

            // Create an UploadedFile instance from the temporary file
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempFilePath,
                $originalFilename,
                mime_content_type($tempFilePath),
                null,
                true // Mark as test file to avoid validation errors
            );

            // Use FileStorageHelper to store the file properly
            $storedPath = FileStorageHelper::storeSinglePrivateFile(
                $uploadedFile,
                $folderName
            );

            // Clean up temporary file
            unlink($tempFilePath);

            return $storedPath;

        } catch (\Exception $e) {
            Log::error('Failed to download and store image', [
                'url' => $imageUrl,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
