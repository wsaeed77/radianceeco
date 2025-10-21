<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class StatusController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Check if user has permissions (optional - can be removed for testing)
        // if (!Auth::user()->hasPermissionTo('status.view')) {
        //     return redirect()->route('dashboard')
        //         ->with('error', 'You do not have permission to view statuses.');
        // }

        $statuses = Status::ordered()->get();

        return Inertia::render('Statuses/Index', [
            'statuses' => $statuses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check if user has permissions (optional - can be removed for testing)
        // if (!Auth::user()->hasPermissionTo('status.create')) {
        //     return redirect()->route('statuses.index')
        //         ->with('error', 'You do not have permission to create statuses.');
        // }

        return Inertia::render('Statuses/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permissions (optional - can be removed for testing)
        // if (!Auth::user()->hasPermissionTo('status.create')) {
        //     return redirect()->route('statuses.index')
        //         ->with('error', 'You do not have permission to create statuses.');
        // }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['sort_order'] = $validated['sort_order'] ?? Status::max('sort_order') + 1;

        Status::create($validated);

        return redirect()->route('statuses.index')
            ->with('success', 'Status created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Check if user has permissions (optional - can be removed for testing)
        // if (!Auth::user()->hasPermissionTo('status.view')) {
        //     return redirect()->route('statuses.index')
        //         ->with('error', 'You do not have permission to view statuses.');
        // }

        $status = Status::findOrFail($id);

        return Inertia::render('Statuses/Show', [
            'status' => $status,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Check if user has permissions (optional - can be removed for testing)
        // if (!Auth::user()->hasPermissionTo('status.edit')) {
        //     return redirect()->route('statuses.index')
        //         ->with('error', 'You do not have permission to edit statuses.');
        // }

        $status = Status::findOrFail($id);

        return Inertia::render('Statuses/Edit', [
            'status' => $status,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Check if user has permissions (optional - can be removed for testing)
        // if (!Auth::user()->hasPermissionTo('status.edit')) {
        //     return redirect()->route('statuses.index')
        //         ->with('error', 'You do not have permission to edit statuses.');
        // }

        $status = Status::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $status->update($validated);

        return redirect()->route('statuses.index')
            ->with('success', 'Status updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Check if user has permissions (optional - can be removed for testing)
        // if (!Auth::user()->hasPermissionTo('status.delete')) {
        //     return redirect()->route('statuses.index')
        //         ->with('error', 'You do not have permission to delete statuses.');
        // }

        $status = Status::findOrFail($id);

        // Check if status is being used by any leads
        if ($status->leads()->count() > 0) {
            return redirect()->route('statuses.index')
                ->with('error', 'Cannot delete status that is being used by leads.');
        }

        $status->delete();

        return redirect()->route('statuses.index')
            ->with('success', 'Status deleted successfully!');
    }
}
