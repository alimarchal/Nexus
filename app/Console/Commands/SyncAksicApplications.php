<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\AksicApplicationController;
use Illuminate\Support\Facades\Log;

class SyncAksicApplications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aksic:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync AKSIC applications from external API automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting AKSIC applications sync...');
        Log::info('AKSIC Scheduler: Starting automatic sync');

        try {
            // Create controller instance and call sync method
            $controller = new AksicApplicationController();
            $response = $controller->syncApplications();

            // Get response data
            $responseData = $response->getData(true);

            if ($responseData['success']) {
                $results = $responseData['results'];
                $this->info("Sync completed successfully!");
                $this->info("Total: {$results['total']} | Created: {$results['created']} | Updated: {$results['updated']} | Failed: {$results['failed']}");

                Log::info('AKSIC Scheduler: Sync completed successfully', $results);
            } else {
                $this->error("Sync failed: " . $responseData['message']);
                Log::error('AKSIC Scheduler: Sync failed', ['message' => $responseData['message']]);
            }

        } catch (\Exception $e) {
            $this->error("Error during sync: " . $e->getMessage());
            Log::error('AKSIC Scheduler: Error during sync', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return 0;
    }
}
