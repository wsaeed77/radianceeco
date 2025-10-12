<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AgentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display a listing of the agents.
     */
    public function index()
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('agent.view')) {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to view agents.');
        }
        
        // Get users with the agent role
        $agents = User::role('agent')->orderBy('name')->get();
        
        return Inertia::render('Agents/Index', [
            'agents' => $agents,
        ]);
    }
    
    /**
     * Show the form for creating a new agent.
     */
    public function create()
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('agent.create')) {
            return redirect()->route('agents.index')
                ->with('error', 'You do not have permission to create agents.');
        }
        
        return Inertia::render('Agents/Create');
    }
    
    /**
     * Store a newly created agent in storage.
     */
    public function store(Request $request)
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('agent.create')) {
            return redirect()->route('agents.index')
                ->with('error', 'You do not have permission to create agents.');
        }
        
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);
        
        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
        ]);
        
        // Assign the agent role
        $user->assignRole('agent');
        
        return redirect()->route('agents.index')
            ->with('success', 'Agent created successfully.');
    }
    
    /**
     * Show the form for editing the specified agent.
     */
    public function edit($id)
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('agent.edit')) {
            return redirect()->route('agents.index')
                ->with('error', 'You do not have permission to edit agents.');
        }
        
        $agent = User::findOrFail($id);
        
        return Inertia::render('Agents/Edit', [
            'agent' => $agent,
        ]);
    }
    
    /**
     * Update the specified agent in storage.
     */
    public function update(Request $request, $id)
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('agent.edit')) {
            return redirect()->route('agents.index')
                ->with('error', 'You do not have permission to edit agents.');
        }
        
        $agent = User::findOrFail($id);
        
        // Validate request
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
        ];
        
        // Only validate password if it's being changed
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }
        
        $validated = $request->validate($rules);
        
        // Update user data
        $agent->name = $validated['name'];
        $agent->email = $validated['email'];
        $agent->phone = $validated['phone'] ?? null;
        
        // Only update password if it's being changed
        if ($request->filled('password')) {
            $agent->password = Hash::make($validated['password']);
        }
        
        $agent->save();
        
        return redirect()->route('agents.index')
            ->with('success', 'Agent updated successfully.');
    }
    
    /**
     * Remove the specified agent from storage.
     */
    public function destroy($id)
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('agent.delete')) {
            return redirect()->route('agents.index')
                ->with('error', 'You do not have permission to delete agents.');
        }
        
        $agent = User::findOrFail($id);
        
        // Check if this agent has any leads assigned
        if ($agent->assignedLeads()->exists()) {
            return redirect()->route('agents.index')
                ->with('error', 'Cannot delete agent. Agent has assigned leads.');
        }
        
        $agent->delete();
        
        return redirect()->route('agents.index')
            ->with('success', 'Agent deleted successfully.');
    }
}