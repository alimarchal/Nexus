<?php

namespace App\Http\Controllers;

use App\Models\DailyPosition;
use App\Models\Region;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Report;

class ReportController extends Controller
{
    // Main report index
    public function index(Request $request)
    {
        $branches = Branch::all(); // Fetch all branches from database
        $reports = Report::query();

        if ($request->filled('date')) {
            $reports->where('date', $request->date);
        }

        if ($request->filled('branch_id')) {
            $reports->where('branch_id', $request->branch_id);
        }

        if ($request->filled('branch_code')) {
            $reports->where('branch_code', $request->branch_code);
        }

        return view('reports.index', [
            'reports' => $reports->get(),
            'branches' => $branches,
        ]);
    }

    // Display the daily position report
    public function dailyPositionReport()
    {
        $date = request('filter.date', Carbon::now()->format('Y-m-d'));
        $data = [];
        $i = 1;
        foreach (Branch::all() as $branch) {

            $data[$i] = [
                'branch_id' => $branch->id,
                'date' => Carbon::parse($date)->format('d-M-Y'),
                'branchName' => $branch->name,
                'branchCode' => $branch->code,
                'status' => 'Missing',
            ];
            $daily_position_status = DailyPosition::where('branch_id', $branch->id)->where('date', $date)->get();
            if ($daily_position_status->isNotEmpty()) {
                $data[$i]['status'] = 'OK';
            }
            $i++;
        }

        return view('reports.daily-position-reports', compact('data'));
    }
}
