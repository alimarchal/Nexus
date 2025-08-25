<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use App\Http\Requests\StoreComplaintRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class HarassmentComplaintValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that harassment incident date validation works correctly for today's date with old rule
     * This test demonstrates the BUG - it should pass but fails with the old rule
     */
    public function test_harassment_incident_date_with_old_rule_fails_for_current_day()
    {
        // Create a datetime for today at a time earlier than current time
        $todayDateTime = Carbon::now()->subHour()->format('Y-m-d\TH:i');
        
        $data = [
            'harassment_incident_date' => $todayDateTime,
        ];

        $rules = [
            'harassment_incident_date' => [
                'nullable',
                'date',
                'before_or_equal:today'  // Old problematic rule
            ],
        ];

        $validator = Validator::make($data, $rules);
        
        // This currently fails with old rule even though it's a valid past time today
        // Demonstrating the bug: even 1 hour ago today is rejected!
        $this->assertTrue($validator->fails(), 'Old rule incorrectly rejects even past times on current day');
        $this->assertArrayHasKey('harassment_incident_date', $validator->errors()->toArray());
    }

    /**
     * Test that harassment incident date validation rejects future dates
     */
    public function test_harassment_incident_date_rejects_future_dates()
    {
        // Create a datetime for tomorrow
        $futureDateTime = Carbon::tomorrow()->setTime(10, 0, 0)->format('Y-m-d\TH:i');
        
        $data = [
            'harassment_incident_date' => $futureDateTime,
        ];

        $rules = [
            'harassment_incident_date' => [
                'nullable',
                'date',
                'before_or_equal:today'
            ],
        ];

        $validator = Validator::make($data, $rules);
        
        // This should fail
        $this->assertTrue($validator->fails(), 'Future dates should be rejected for harassment incident date');
    }

    /**
     * Test that harassment incident date validation allows past dates
     */
    public function test_harassment_incident_date_allows_past_dates()
    {
        // Create a datetime for yesterday
        $pastDateTime = Carbon::yesterday()->setTime(15, 30, 0)->format('Y-m-d\TH:i');
        
        $data = [
            'harassment_incident_date' => $pastDateTime,
        ];

        $rules = [
            'harassment_incident_date' => [
                'nullable',
                'date',
                'before_or_equal:today'
            ],
        ];

        $validator = Validator::make($data, $rules);
        
        // This should pass
        $this->assertFalse($validator->fails(), 'Past dates should be allowed for harassment incident date');
    }

    /**
     * Test that harassment incident date validation with 'now' rule works correctly
     */
    public function test_harassment_incident_date_with_now_rule_allows_current_time()
    {
        // Create a datetime for current moment minus 1 minute (should pass)
        $currentDateTime = Carbon::now()->subMinute()->format('Y-m-d\TH:i');
        
        $data = [
            'harassment_incident_date' => $currentDateTime,
        ];

        $rules = [
            'harassment_incident_date' => [
                'nullable',
                'date',
                'before_or_equal:now'  // Fixed rule
            ],
        ];

        $validator = Validator::make($data, $rules);
        
        // This should pass
        $this->assertFalse($validator->fails(), 'Current time (minus buffer) should be allowed with now rule');
    }

    /**
     * Test that harassment incident date validation with 'now' rule rejects future times
     */
    public function test_harassment_incident_date_with_now_rule_rejects_future_time()
    {
        // Create a datetime for current moment plus 1 hour (should fail)
        $futureDateTime = Carbon::now()->addHour()->format('Y-m-d\TH:i');
        
        $data = [
            'harassment_incident_date' => $futureDateTime,
        ];

        $rules = [
            'harassment_incident_date' => [
                'nullable',
                'date',
                'before_or_equal:now'  // Fixed rule
            ],
        ];

        $validator = Validator::make($data, $rules);
        
        // This should fail
        $this->assertTrue($validator->fails(), 'Future times should be rejected with now rule');
    }
}