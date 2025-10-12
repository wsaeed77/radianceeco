@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users me-2"></i> {{ __('Agents Management') }}
                    </h6>
                    <div>
                        @can('agent.create')
                            <a href="{{ route('agents.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i> Add New Agent
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th class="text-center">Assigned Leads</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($agents as $agent)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle text-white bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                    style="width: 40px; height: 40px;">
                                                    <span>{{ strtoupper(substr($agent->name, 0, 1)) }}</span>
                                                </div>
                                                <div>{{ $agent->name }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $agent->email }}</td>
                                        <td>{{ $agent->phone ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $agent->assignedLeads->count() }}</span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end">
                                                <a href="{{ route('agents.edit', $agent->id) }}" class="btn btn-sm btn-info me-2">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                
                                                @if($agent->assignedLeads->count() === 0)
                                                    <form action="{{ route('agents.destroy', $agent->id) }}" method="POST" 
                                                        onsubmit="return confirm('Are you sure you want to delete this agent?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-2x mb-3"></i>
                                                <p>No agents found.</p>
                                                @can('agent.create')
                                                    <a href="{{ route('agents.create') }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-plus me-1"></i> Add New Agent
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection