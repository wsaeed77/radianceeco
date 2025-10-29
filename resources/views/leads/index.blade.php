@extends('layouts.app')

@section('styles')
<style>
    .status-text, .address-text {
        display: inline-block;
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }
    
    .address-text {
        max-width: 230px; /* Give address a bit more room */
    }
    
    .btn-icon {
        background: none;
        border: none;
        padding: 0.25rem 0.5rem;
        font-size: 1rem;
        transition: transform 0.2s;
    }
    
    .btn-icon:hover {
        transform: scale(1.2);
    }
    
    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 0.75rem;
    }
    
    /* Make table more responsive */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.9rem;
        }
        
        .status-text {
            max-width: 120px;
        }
        
        .address-text {
            max-width: 150px;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Leads') }}</span>
                    <div>
                        @can('lead.create')
                            <a href="{{ route('leads.create') }}" class="btn btn-sm btn-success me-2">Add New Lead</a>
                        @endcan
                        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-secondary">Back to Dashboard</a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('leads.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone, postcode, or agent" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <select name="status" class="form-control">
                                    <option value="">-- Select Status --</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <select name="stage" class="form-control">
                                    <option value="">-- Select Team --</option>
                                    @foreach($stages as $team)
                                        <option value="{{ $team->value }}" {{ request('stage') == $team->value ? 'selected' : '' }}>
                                            {{ $team->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="{{ route('leads.index') }}" class="btn btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>

                    <!-- Leads Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Postcode</th>
                                    <th>Agent</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Team</th>
                                    <th>Created</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leads as $lead)
                                    <tr>
                                        <td>{{ $lead->first_name }} {{ $lead->last_name }}</td>
                                        <td>
                                            <span class="address-text">
                                                @if($lead->address_line)
                                                    {{ $lead->address_line }}
                                                @elseif($lead->house_number || $lead->street_name || $lead->city || $lead->postcode)
                                                    {{ $lead->house_number }} {{ $lead->street_name }}{{ $lead->city ? ', '.$lead->city : '' }}{{ $lead->postcode ? ', '.$lead->postcode : '' }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-monospace">
                                                {{ $lead->postcode ?: $lead->zip_code ?: '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span>
                                                {{ $lead->agent ? $lead->agent->name : 'Not assigned' }}
                                            </span>
                                        </td>
                                        <td>{{ $lead->phone }}</td>
                                        <td>
                                            <span class="status-text">
                                                {{ $lead->status->label() }}
                                            </span>
                                        </td>
                                        <td>{{ $lead->stage->label() }}</td>
                                        <td>{{ $lead->created_at->format('M d, Y') }}</td>
                                        <td class="text-center">
                                            <div class="action-buttons">
                                                <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="View Lead">
                                                    <i class="fas fa-eye text-info"></i>
                                                </a>
                                                @can('lead.edit')
                                                    <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-sm btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Lead">
                                                        <i class="fas fa-edit text-primary"></i>
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No leads found matching your criteria.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $leads->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush