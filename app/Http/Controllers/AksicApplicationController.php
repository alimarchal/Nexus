<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAksicApplicationRequest;
use App\Http\Requests\UpdateAksicApplicationRequest;
use App\Models\AksicApplication;
use App\Models\AksicApplicationEducation;
use App\Models\AksicApplicationStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Carbon\Carbon;
use App\Helpers\FileStorageHelper;

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
            ->with(['educations', 'statusLogs'])              // Eager load relationships
            ->latest()                                        // Order by newest first
            ->paginate(10);                                   // Paginate results

        return view('aksic-applications.index', compact('applications'));
    }

    /**
     * Download image from URL and store in private storage
     */
    private function downloadAndStoreImage($imageUrl, $folderPath, $fileName)
    {
        try {
            if (!$imageUrl) {
                return null;
            }

            // Download the image with SSL verification disabled
            $response = Http::withOptions(['verify' => false])
                ->timeout(30)
                ->get($imageUrl);

            if (!$response->successful()) {
                Log::warning("Failed to download image from URL: {$imageUrl}");
                return null;
            }

            // Get image content
            $imageContent = $response->body();

            // Get file extension from URL or default to jpg
            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $fullFileName = $fileName . '.' . $extension;

            // Store the file in private storage
            $fullPath = $folderPath . '/' . $fullFileName;
            Storage::disk('local')->put($fullPath, $imageContent);

            Log::info("Successfully downloaded and stored image", [
                'url' => $imageUrl,
                'stored_path' => $fullPath
            ]);

            return $fullPath;

        } catch (\Exception $e) {
            Log::error("Error downloading image from URL: {$imageUrl}", [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Sync applications from external API
     */
    public function syncApplications()
    {
        try {
            Log::info('Starting AKSIC applications sync from API');

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

            // Process each application in a transaction
            foreach ($applications as $appData) {
                try {
                    DB::beginTransaction();

                    // Create folder structure for this applicant's files
                    $applicantFolderPath = 'AKSIC/' . $appData['id'];

                    // Download and store images from URLs
                    $challanImagePath = null;
                    $cnicFrontPath = null;
                    $cnicBackPath = null;

                    if (isset($appData['challan_image_url'])) {
                        $challanImagePath = $this->downloadAndStoreImage(
                            $appData['challan_image_url'],
                            $applicantFolderPath,
                            'challan_' . $appData['challan_image']
                        );
                    }

                    if (isset($appData['cnic_front_url'])) {
                        $cnicFrontPath = $this->downloadAndStoreImage(
                            $appData['cnic_front_url'],
                            $applicantFolderPath,
                            'cnic_front_' . $appData['cnic_front']
                        );
                    }

                    if (isset($appData['cnic_back_url'])) {
                        $cnicBackPath = $this->downloadAndStoreImage(
                            $appData['cnic_back_url'],
                            $applicantFolderPath,
                            'cnic_back_' . $appData['cnic_back']
                        );
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
                        // Store local file paths instead of original filenames
                        'challan_image' => $challanImagePath ?? $appData['challan_image'],
                        'cnic_front' => $cnicFrontPath ?? $appData['cnic_front'],
                        'cnic_back' => $cnicBackPath ?? $appData['cnic_back'],
                        // Store original API URLs
                        'challan_image_url' => $appData['challan_image_url'] ?? null,
                        'cnic_front_url' => $appData['cnic_front_url'] ?? null,
                        'cnic_back_url' => $appData['cnic_back_url'] ?? null,
                        'fee_status' => $appData['fee_status'],
                        'status' => $appData['status'],
                        'bank_status' => $appData['bank_status'],
                        'fee_branch_code' => $appData['fee_branch']['branch_code'] ?? null,
                        'district_name' => $appData['district']['name'] ?? null,
                        'tehsil_name' => $appData['tehsil']['name'] ?? null,
                        'api_call_json' => $appData, // Store entire API response including URLs
                    ];

                    // Check if application exists
                    $existingApp = AksicApplication::where('applicant_id', $appData['id'])->first();

                    if ($existingApp) {
                        // Clean up old image files if they exist
                        $oldImageFiles = [
                            $existingApp->challan_image,
                            $existingApp->cnic_front,
                            $existingApp->cnic_back
                        ];

                        foreach ($oldImageFiles as $oldFile) {
                            if ($oldFile && Storage::disk('local')->exists($oldFile)) {
                                Storage::disk('local')->delete($oldFile);
                                Log::info('Deleted old image file', ['file' => $oldFile]);
                            }
                        }

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
                                'aksic_id' => $appData['id'],
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
                                'aksic_id' => $appData['id'],
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
        //
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
}
