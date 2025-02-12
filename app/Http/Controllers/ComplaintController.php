<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\UpdateComplaintRequest;
use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintStatusType;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::with(['status', 'createdBy', 'assignedTo'])->latest()->paginate(10);
        return view('complaints.index', compact('complaints'));
    }

    public function create()
    {
        $statuses = ComplaintStatusType::all();

        // Debug: Log the retrieved statuses
        Log::info('Statuses Retrieved:', ['statuses' => $statuses->toArray()]);

        $divisions = Division::all();
        $users = User::all();

        return view('complaints.create', compact('statuses', 'divisions', 'users'));
    }





    public function show(Complaint $complaint)
    {
        return view('complaints.show', compact('complaint'));
    }

    public function edit(Complaint $complaint)
    {
        $statuses = ComplaintStatusType::all();
        $divisions = Division::all();
        $users = User::whereIn('id', Division::pluck('id'))->get();
        return view('complaints.edit', compact('complaint', 'statuses', 'divisions', 'users'));
    }

    public function update(UpdateComplaintRequest $request, Complaint $complaint)
    {
        $complaint->update($request->validated());

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('complaint_attachments');
                ComplaintAttachment::create([
                    'complaint_id' => $complaint->id,
                    'file_path' => $path,
                ]);
            }
        }

        return redirect()->route('complaints.index')->with('success', 'Complaint updated successfully.');
    }

    public function destroy(Complaint $complaint)
    {
        foreach ($complaint->attachments as $attachment) {
            Storage::delete($attachment->file_path);
            $attachment->delete();
        }
        $complaint->delete();

        return redirect()->route('complaints.index')->with('success', 'Complaint deleted successfully.');
    }
}