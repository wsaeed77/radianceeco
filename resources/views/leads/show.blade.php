@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Lead Details') }}</span>
                    <div>
                        @can('lead.edit')
                            <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-sm btn-primary me-2">Edit Lead</a>
                        @endcan
                        <a href="{{ route('leads.index') }}" class="btn btn-sm btn-secondary me-2">Back to Leads</a>
                        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-secondary">Dashboard</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Lead Information -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    Lead Information
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Name:</div>
                                        <div class="col-md-8">{{ $lead->first_name }} {{ $lead->last_name }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Email:</div>
                                        <div class="col-md-8">{{ $lead->email }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Phone:</div>
                                        <div class="col-md-8">{{ $lead->phone }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Status:</div>
                                        <div class="col-md-8">{{ $lead->status->label() }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Team:</div>
                                        <div class="col-md-8">{{ $lead->stage->label() }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Address:</div>
                                        <div class="col-md-8">
                                            {{ $lead->address_line_1 }}<br>
                                            @if($lead->address_line_2)
                                                {{ $lead->address_line_2 }}<br>
                                            @endif
                                            {{ $lead->city }}, {{ $lead->assigned_to }} {{ $lead->zip_code }}
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Created:</div>
                                        <div class="col-md-8">{{ $lead->created_at->format('M d, Y H:i:s') }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Last Updated:</div>
                                        <div class="col-md-8">{{ $lead->updated_at->format('M d, Y H:i:s') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    Additional Information
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Source:</div>
                                        <div class="col-md-8">{{ $lead->source ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Source Details:</div>
                                        <div class="col-md-8">{{ $lead->source_details ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Grant Type:</div>
                                        <div class="col-md-8">{{ $lead->grant_type ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Is Duplicate:</div>
                                        <div class="col-md-8">{{ $lead->is_duplicate ? 'Yes' : 'No' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Is Complete:</div>
                                        <div class="col-md-8">{{ $lead->is_complete ? 'Yes' : 'No' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Notes:</div>
                                        <div class="col-md-8">{{ $lead->notes ?? 'No notes available' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Assigned Agent:</div>
                                        <div class="col-md-8">{{ $lead->agent_id && $lead->assignedAgent ? $lead->assignedAgent->name : 'Not Assigned' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Data Match Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-purple text-white d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-database me-2"></i> Data Match</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">Benefit Holder Name:</div>
                                                <div class="col-md-7">{{ $lead->benefit_holder_name ?? 'Not specified' }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">Benefit Holder DOB:</div>
                                                <div class="col-md-7">{{ $lead->benefit_holder_dob ? $lead->benefit_holder_dob->format('d/m/Y') : 'Not specified' }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">Data Match Status:</div>
                                                <div class="col-md-7">
                                                    @if($lead->data_match_status)
                                                        <span class="badge bg-{{ 
                                                            $lead->data_match_status == 'Matched' ? 'success' : (
                                                            $lead->data_match_status == 'Pending' ? 'warning' : (
                                                            $lead->data_match_status == 'Sent' ? 'info' : (
                                                            $lead->data_match_status == 'Unmatched' ? 'danger' : 'secondary')))
                                                        }}">
                                                            {{ $lead->data_match_status }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">Not specified</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">Phone Numbers:</div>
                                                <div class="col-md-7">
                                                    @if($lead->multi_phone_numbers && count($lead->multi_phone_numbers) > 0)
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach($lead->multi_phone_numbers as $phone)
                                                                <li><strong>{{ $phone['label'] ?? 'Phone' }}:</strong> {{ $phone['number'] }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span class="text-muted">No phone numbers added</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="card bg-light">
                                                <div class="card-body pb-1">
                                                    <div class="row mb-2">
                                                        <div class="col-md-3 fw-bold">Data Match Remarks:</div>
                                                        <div class="col-md-9">{{ $lead->data_match_remarks ?? 'Not specified' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Eligibility Details -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-check-circle me-2"></i> Eligibility Details</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">Occupancy Type:</div>
                                                <div class="col-md-7">
                                                    @if($lead->occupancy_type)
                                                        <span class="badge bg-{{ $lead->occupancy_type == 'owner' ? 'primary' : 'info' }}">
                                                            {{ ucfirst($lead->occupancy_type) }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">Not specified</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">Client DOB:</div>
                                                <div class="col-md-7">{{ $lead->eligibility_client_dob ? $lead->eligibility_client_dob->format('d/m/Y') : 'Not specified' }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">Possible Grant:</div>
                                                <div class="col-md-7">{{ $lead->possible_grant_types ?? 'Not specified' }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">Benefit:</div>
                                                <div class="col-md-7">{{ $lead->benefit_type ?? 'Not specified' }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">Proof of Address:</div>
                                                <div class="col-md-7">{{ $lead->poa_info ?? 'Not specified' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">EPC Rating:</div>
                                                <div class="col-md-7">
                                                    @if($lead->epc_rating)
                                                        <span class="badge bg-{{ 
                                                            $lead->epc_rating == 'A' ? 'success' : (
                                                            $lead->epc_rating == 'B' ? 'success' : (
                                                            $lead->epc_rating == 'C' ? 'info' : (
                                                            $lead->epc_rating == 'D' ? 'info' : (
                                                            $lead->epc_rating == 'E' ? 'warning' : 'danger'))))
                                                        }}">
                                                            {{ $lead->epc_rating }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">Not specified</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">EPC Details:</div>
                                                <div class="col-md-7">{{ $lead->epc_details ?? 'Not specified' }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">GAS SAFE:</div>
                                                <div class="col-md-7">{{ $lead->gas_safe_info ?? 'Not specified' }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-5 fw-bold">Council Tax Band:</div>
                                                <div class="col-md-7">
                                                    @if($lead->council_tax_band)
                                                        <span class="badge bg-secondary">Band {{ $lead->council_tax_band }}</span>
                                                    @else
                                                        <span class="text-muted">Not specified</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Thread -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                    <span>Activity Thread</span>
                                    <div>
                                        @can('lead.edit')
                                            <a href="{{ route('activities.create', $lead->id) }}" class="btn btn-sm btn-light">Add Activity</a>
                                        @endcan
                                    </div>
                                </div>
                                <div class="card-body">
                                    @forelse($lead->activities->sortByDesc('created_at') as $activity)
                                        <div class="activity-item mb-4">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <!-- User avatar or icon based on activity type -->
                                                    <div class="activity-avatar rounded-circle bg-{{ $activity->type->value == 'note' ? 'primary' : ($activity->type->value == 'status_change' ? 'warning' : ($activity->type->value == 'file_upload' ? 'info' : 'secondary')) }}" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; color: white;">
                                                        <i class="fas {{ $activity->type->value == 'note' ? 'fa-comment' : ($activity->type->value == 'status_change' ? 'fa-exchange-alt' : ($activity->type->value == 'file_upload' ? 'fa-file' : 'fa-tasks')) }}"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <h6 class="mb-0">
                                                            <span class="fw-bold">{{ $activity->user->name ?? 'System' }}</span>
                                                            <small class="text-muted">{{ $activity->type->name }}</small>
                                                        </h6>
                                                        <small class="text-muted">{{ $activity->created_at->format('M d, Y H:i') }}</small>
                                                    </div>
                                                    <div class="activity-content p-3 bg-light rounded">
                                                        <p class="mb-1">{{ $activity->description }}</p>
                                                        @if($activity->message)
                                                            <p class="mb-0 text-muted small">{{ $activity->message }}</p>
                                                        @endif
                                                        
                                                        <!-- Display documents attached to this activity -->
                                                        @if($activity->documents->count() > 0)
                                                            <div class="mt-2 pt-2 border-top">
                                                                <p class="mb-1 small fw-bold">Attached Documents:</p>
                                                                <ul class="list-unstyled">
                                                                    @foreach($activity->documents as $document)
                                                                        <li class="small">
                                                                            <i class="fas fa-paperclip me-1"></i>
                                                                            <a href="{{ route('documents.download', $document->id) }}">{{ $document->name }}</a>
                                                                            <span class="text-muted">({{ number_format($document->size_bytes / 1024, 2) }} KB)</span>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="activity-actions mt-1">
                                                        @can('lead.edit')
                                                            <a href="{{ route('activities.edit', $activity->id) }}" class="btn btn-sm btn-link p-0">Edit</a> |
                                                            <a href="{{ route('documents.create.activity', ['lead' => $lead->id, 'activity' => $activity->id]) }}" class="btn btn-sm btn-link p-0">Attach Document</a> |
                                                            <form method="POST" action="{{ route('activities.destroy', $activity->id) }}" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm('Are you sure?')">Delete</button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-4">
                                            <p class="text-muted mb-0">No activity history found.</p>
                                        </div>
                                    @endforelse

                                    <!-- Quick Add Activity Form -->
                                    @can('lead.edit')
                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="mb-3">Add New Activity</h6>
                                        <form method="POST" action="{{ route('activities.store') }}" class="quick-activity-form">
                                            @csrf
                                            <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                                            
                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <select class="form-select form-select-sm @error('type') is-invalid @enderror" name="type" required>
                                                        <option value="">Select Type</option>
                                                        @foreach(\App\Enums\ActivityType::cases() as $activityType)
                                                            <option value="{{ $activityType->value }}">
                                                                {{ $activityType->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control form-control-sm @error('description') is-invalid @enderror" 
                                                        name="description" placeholder="Description" required>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <textarea class="form-control form-control-sm @error('message') is-invalid @enderror" 
                                                    name="message" rows="3" placeholder="Details (optional)"></textarea>
                                            </div>
                                            
                                            <div class="d-flex justify-content-end">
                                                <button type="submit" class="btn btn-sm btn-success">Add Activity</button>
                                            </div>
                                        </form>
                                    </div>
                                    @endcan
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <span>Documents</span>
                                    <div>
                                        @can('lead.edit')
                                            <a href="{{ route('documents.create', $lead->id) }}" class="btn btn-sm btn-light">Add Document</a>
                                        @endcan
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Kind</th>
                                                    <th>Size</th>
                                                    <th>Uploaded</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($lead->documents as $document)
                                                    <tr>
                                                        <td>{{ $document->name }}</td>
                                                        <td>{{ $document->kind->name }}</td>
                                                        <td>{{ number_format($document->size_bytes / 1024, 2) }} KB</td>
                                                        <td>{{ $document->created_at->format('M d, Y') }}</td>
                                                        <td>
                                                            <a href="{{ route('documents.download', $document->id) }}" class="btn btn-sm btn-primary">Download</a>
                                                            @can('lead.edit')
                                                                <form method="POST" action="{{ route('documents.destroy', $document->id) }}" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                                                </form>
                                                            @endcan
                                                            @if($document->activity_id)
                                                                <a href="{{ route('activities.edit', $document->activity_id) }}" class="btn btn-sm btn-info">View Activity</a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center">No documents found.</td>
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
            </div>
        </div>
    </div>
</div>
@endsection