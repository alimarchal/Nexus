<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Circular;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreCircularRequest;
use App\Http\Requests\UpdateCircularRequest;
use App\Helpers\FileStorageHelper;
use Illuminate\Support\Facades\DB;

class CircularController extends Controller
{

    public function index(Request $request)
    {
        $circulars = QueryBuilder::for(Circular::class)
            ->allowedFilters([
                AllowedFilter::exact('division_id'),
                AllowedFilter::partial('circular_no'),
                AllowedFilter::callback('date_from', function ($query, $value) {
                    $query->whereDate('created_at', '>=', $value);
                }),
                AllowedFilter::callback('date_to', function ($query, $value) {
                    $query->whereDate('created_at', '<=', $value);
                })
            ])
            ->with(['user', 'division', 'updatedBy'])
            ->latest()
            ->paginate(10);

        $divisions = Division::all();

        return view('circulars.index', compact('circulars', 'divisions'));
    }

    public function create()
    {
        // Manually check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to create a circular.');
        }

        $divisions = Division::all(); // Fetch all divisions
        return view('circulars.create', compact('divisions'));
    }

    public function store(StoreCircularRequest $request)
    {
        DB::beginTransaction();

        $folderName = 'Circulars/' . Division::find($request->division_id)->name;
        try {
            $validated = $request->validated();
            // Handle file upload using FileStorageHelper
            if ($request->hasFile('attachment')) {
                $validated['attachment'] = FileStorageHelper::storeSinglePrivateFile(
                    $request->file('attachment'),
                    $folderName,
                    $validated['circular_no'] ?? null
                );
            }

            $circular = Circular::create($validated);

            DB::commit();

            return redirect()
                ->route('circulars.index')
                ->with('success', "Circular '{$circular->title}' created successfully with number: {$circular->circular_no}");

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'circular_no')) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Circular number already exists. Please use a different number.');
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error occurred. Please try again.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create circular. Please try again.');
        }
    }

    public function update(UpdateCircularRequest $request, Circular $circular)
    {
        DB::beginTransaction();

        $folderName = 'Circulars/' . Division::find($request->division_id)->name;
        try {
            $validated = $request->validated();

            // Handle file replacement using FileStorageHelper
            if ($request->hasFile('attachment')) {
                // Delete old file if exists
                if ($circular->attachment) {
                    FileStorageHelper::deleteFile($circular->attachment);
                }

                // Store new file
                $validated['attachment'] = FileStorageHelper::storeSingleFile(
                    $request->file('attachment'),
                    $folderName,
                    $circular->circular_no
                );
            }

            $isUpdated = $circular->update($validated);

            if (!$isUpdated) {
                DB::rollBack();
                return redirect()->back()
                    ->with('info', 'No changes were made to the circular.');
            }

            DB::commit();

            Log::info('Circular updated successfully', [
                'circular_id' => $circular->id,
                'circular_no' => $circular->circular_no,
                'updated_by' => auth()->id(),
                'changes' => $circular->getChanges()
            ]);

            return redirect()
                ->route('circulars.index')
                ->with('success', "Circular '{$circular->title}' updated successfully.");

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'circular_no')) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Circular number already exists. Please use a different number.');
            }

            Log::error('Database error updating circular', [
                'circular_id' => $circular->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error occurred. Please try again.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating circular', [
                'circular_id' => $circular->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update circular. Please try again.');
        }
    }

    public function show(Circular $circular)
    {
        // Manually check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to view the circular.');
        }

        $circular->load(['user', 'division', 'updatedBy']);
        return view('circulars.show', compact('circular'));
    }

    public function edit(Circular $circular)
    {

        $divisions = Division::all();
        $users = User::all();
        return view('circulars.edit', compact('circular', 'divisions', 'users'));
    }
}