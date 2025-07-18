<?php

namespace App\Http\Controllers;
use App\Models\ProjectOutlook;
use App\Models\State;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectOutlookController extends Controller
{
     use AuthorizesRequests;
    public function index()
    {
        $this->authorize('view_project_outlooks');
        
        return Inertia::render('ProjectOutlooks/Index', [
            'outlooks' => ProjectOutlook::with(['state', 'lga'])
                ->latest()
                ->paginate(10),
            'states' => State::with('lgas')->get(),
            'years' => range(date('Y'), date('Y') + 5) // Next 5 years
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create_project_outlooks');
        
        $validated = $request->validate([
            'state_id' => 'required|exists:states,id',
            'lga_id' => 'required|exists:lgas,id',
            'outlook' => 'required|integer|min:1',
            'project_year' => 'required|digits:4|integer|min:'.date('Y')
        ]);

        ProjectOutlook::create($validated);

        return redirect()->route('project-outlooks.index')
            ->with('success', 'Project outlook created successfully.');
    }

    public function update(Request $request, ProjectOutlook $projectOutlook)
    {
        $this->authorize('edit_project_outlooks', $projectOutlook);
        
        $validated = $request->validate([
            'outlook' => 'required|integer|min:0',
            'project_year' => 'required|digits:4|integer|min:'.date('Y')
        ]);

        $projectOutlook->update($validated);

        return redirect()->route('project-outlooks.index')
            ->with('success', 'Project outlook updated successfully.');
    }

    public function destroy(ProjectOutlook $projectOutlook)
    {
        $this->authorize('delete_project_outlooks', $projectOutlook);
        
        $projectOutlook->delete();

        return redirect()->route('project-outlooks.index')
            ->with('success', 'Project outlook deleted successfully.');
    }


}
