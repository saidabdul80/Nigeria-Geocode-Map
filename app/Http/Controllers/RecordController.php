<?php

namespace App\Http\Controllers;

use App\Models\Lga;
use App\Models\Record;
use App\Models\State;
use App\Models\Ward;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecordController extends Controller
{
    use    AuthorizesRequests;
    public function index()
    {
        
        $this->authorize('view_records');

        $query = Record::with(['state', 'lga'])
            ->latest();

        // Filter by user permissions if not admin
        if (!auth()->user()->hasRole('admin')) {
            $query->where(function ($q) {
                // Users with state permissions
                if (auth()->user()->statePermissions()->exists()) {
                    $q->whereIn('state_id', auth()->user()->statePermissions->pluck('id'));
                }

                // Users with LGA permissions
                if (auth()->user()->lgaPermissions()->exists()) {
                    $q->orWhereIn('lga_id', auth()->user()->lgaPermissions->pluck('id'));
                }
            });
        }

        return Inertia::render('Records/Index', [
            'records' => $query->paginate(10),
            'states' => $this->getAccessibleStates(),
            'options' => ['change']
            //'options' => ['change', 'wind', 'rain']
        ]);
    }

    // Add this helper method
    protected function getAccessibleStates()
    {
        if (auth()->user()->hasRole('admin')) {
            return State::with('lgas')->get();
        }

        return State::with('lgas')
            ->whereHas('userStatePermissions', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->orWhereHas('lgas.userLgaPermissions', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->get();
    }

    // Update store method
    public function store(Request $request)
    {
        $this->authorize('create_records');

        $validated = $request->validate([
            'state_id' => [
                'required',
                'exists:states,id',
                function ($attribute, $value, $fail) {
                    if (!auth()->user()->canAccessState($value)) {
                        $fail('You do not have permission to create records for this state.');
                    }
                }
            ],
            'lga_id' => [
                'required',
                'exists:lgas,id',
                function ($attribute, $value, $fail) use ($request) {
                    if (!auth()->user()->canAccessLga($value)) {
                        $fail('You do not have permission to create records for this LGA.');
                    }

                    // Also verify the LGA belongs to the selected state
                    // $lga = Lga::find($value);
                    // if ($lga && $lga->state_id != $request->state_id) {
                    //     $fail('The selected LGA does not belong to the selected state.');
                    // }
                }
            ],
            'record' => 'required|array',
            'record.*.key' => 'required|string',
            'record.*.outlook' => 'required|string',
            'record.*.value' => 'nullable|string',
        ]);

        // Rest of the method remains the same
        // $formattedData = collect($validated['record'])
        //     ->pluck('value', 'key')
        //     ->toArray();

        $ward = Ward::find($request->ward_id);
        if (!auth()->user()->canAccessWard($ward->id)) {
            abort(403, 'You do not have permission to create records for this ward.');
        }

        Record::create([
            'state_id' => $validated['state_id'],
            'lga_id' => $validated['lga_id'],
            'ward_id' => $request->ward_id, 
            'data' => $request->record 
        ]);
        return redirect()->route('records.index')
            ->with('success', 'Record created successfully.');
    }


    /**
     * Display the specified resource.
     */
    public function show(Record $record)
    {
        return Inertia::render('Records/Show', [
            'record' => $record->load(['state', 'lga'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Record $record)
    {
        // Handled by Inertia frontend
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Record $record)
    {
        $validated = $request->validate([
            'state_id' => 'required|exists:states,id',
            'lga_id' => 'required|exists:lgas,id',
            'record' => 'required|array'
        ]);

        // Convert array of {key,value} objects to associative array
    

        $record->update([
            'state_id' => $validated['state_id'],
            'lga_id' => $validated['lga_id'],
            'ward_id' => $request->ward_id, 
            'data' => $request->record
        ]);

        return redirect()->route('records.index')
            ->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Record $record)
    {
        $record->delete();

        return redirect()->route('records.index')
            ->with('success', 'Record deleted successfully.');
    }

    /**
     * API endpoint to get LGAs for a state
     */
    public function getLgas(Request $request)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id'
        ]);

        return response()->json(
            \App\Models\Lga::where('state_id', $request->state_id)->get()
        );
    }


    public function dashboard(Request $request, $state = null)
    {
        $heatmapData = Record::with('lga');
        if ($state != 'nigeria') {
            $query = $state;
            if ($state == 'akwaibom') {
                $query = 'akwa';
            }

            if ($state == 'crossriver') {
                $query = 'cross';
            }

            $heatmapData->whereHas('state', function ($q) use ($query) {
                $q->where('name', 'like', "%$query%");
            });
        }

        $heatmapData = $heatmapData->get();
            //->groupBy('lga_id')
            if($state == 'nigeria'){
                $heatmapData = $heatmapData->groupBy('state_id');
            }else{
                $heatmapData = $heatmapData->groupBy('ward_id');
            }

        $heatmapData = $heatmapData->map(function ($records, $lgaId) {
                $lga = $records->first()->lga;
                $totalOutlookChange = $records->sum(function ($record) {
                    // $rc = collect($record->data)->firstWhere('key','change');
                    // return intval($rc['outlook'] ?? 0);
                     return collect($record->data)
                        ->filter(fn($item) => $item['key'] === 'change')
                        ->sum(fn($item) => intval($item['outlook'] ?? 0));
                });
                $totalChange = $records->sum(function ($record) {
                    // $rc = collect($record->data)->firstWhere('key','change');
                    // return intval($rc['value'] ?? 0);
                     return collect($record->data)
                        ->filter(fn($item) => $item['key'] === 'change')
                        ->sum(fn($item) => intval($item['value'] ?? 0));
                });

             // Calculate percentage (handle division by zero)
                $percentage = $totalOutlookChange != 0 
                    ? ($totalChange / $totalOutlookChange) * 100 
                    : 0;

                return [
                    'lat' => $lga->latitude,
                    'lng' => $lga->longitude,
                    'change' => $totalChange,
                    'outlook' => $totalOutlookChange,
                    'percentage' => round($percentage, 2),
                    'lga_name' => $lga->name,
                    'state_name' => $lga->state->name
                ];
            })
            ->values()
            ->toArray();
            
        return Inertia::render('Dashboard', [
            'region' => $state ?? '',
            'heatData' => $heatmapData,
        ]);
    }
}
