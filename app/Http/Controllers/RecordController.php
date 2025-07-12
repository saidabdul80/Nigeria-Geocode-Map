<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\State;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Records/Index', [
            'records' => Record::with(['state', 'lga'])
                ->latest()
                ->paginate(10),
            'states' => State::with('lgas')->get(),
            'options' => ['change', 'wind', 'rain']
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Handled by Inertia frontend
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'state_id' => 'required|exists:states,id',
            'lga_id' => 'required|exists:lgas,id',
            'record' => 'required|array',
            'record.*.key' => 'required|string',
            'record.*.value' => 'required'
        ]);

        // Convert array of {key,value} objects to associative array
        $formattedData = collect($validated['record'])
            ->pluck('value', 'key')
            ->toArray();

        Record::create([
            'state_id' => $validated['state_id'],
            'lga_id' => $validated['lga_id'],
            'data' => $formattedData
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
            'data' => 'required|array',
            'data.*.key' => 'required|string',
            'data.*.value' => 'required'
        ]);

        // Convert array of {key,value} objects to associative array
        $formattedData = collect($validated['data'])
            ->pluck('value', 'key')
            ->toArray();

        $record->update([
            'state_id' => $validated['state_id'],
            'lga_id' => $validated['lga_id'],
            'data' => $formattedData
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


    public function dashboard(Request $request, $state = null){
        $heatmapData = Record::with('lga');
        if($state != 'nigeria'){
            $query = $state;
            if($state == 'akwaibom'){
                $query = 'akwa';
            }

            if($state == 'crossriver'){
                $query = 'cross';
            }
            
            $heatmapData->whereHas('state',function($q) use($query){
                $q->where('name','like', "%$query%");
            });
        }

        $heatmapData = $heatmapData->get()
            ->groupBy('lga_id')
            ->map(function ($records, $lgaId) {
                $lga = $records->first()->lga;
                $totalChange = $records->sum(function ($record) {
                    return $record->data['change'] ?? 0;
                });
                
                return [
                    'lat' => $lga->latitude,
                    'lng' => $lga->longitude,
                    'change' => $totalChange,
                    'lga_name' => $lga->name,
                    'state_name' => $lga->state->name
                ];
            })
            ->values()
            ->toArray();

        return Inertia::render('Dashboard',[
            'region' => $state??'',
            'heatData' => $heatmapData,
        ]);
    }
}