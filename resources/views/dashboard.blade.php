@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="card bg-primary text-white shadow mb-4">
        <div class="card-body py-4">
            <div class="d-sm-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h3 mb-0 text-white">
                        <i class="fas fa-tachometer-alt me-2"></i> {{ __('Dashboard') }}
                    </h1>
                    <p class="mb-0 opacity-75">Welcome to Radiance Eco Lead Management System</p>
                </div>
                <div class="mt-3 mt-sm-0">
                    <a href="{{ route('leads.create') }}" class="btn btn-light text-primary shadow-sm">
                        <i class="fas fa-plus fa-sm me-2"></i> Add New Lead
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row mb-4">
        <!-- Total Leads -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-4 border-primary shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Leads</div>
                            <div class="d-flex align-items-center">
                                <div class="h3 mb-0 fw-bold text-dark me-2">{{ $totalLeads }}</div>
                                <div class="small text-success">
                                    <i class="fas fa-chart-line me-1"></i> Lead Management
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-primary bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-users fa-lg text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Installed Leads -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-4 border-success shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Installed</div>
                            <div class="d-flex align-items-center">
                                <div class="h3 mb-0 fw-bold text-dark me-2">{{ $statusCounts['installed'] ?? 0 }}</div>
                                @php
                                    $percentage = $totalLeads > 0 ? round((($statusCounts['installed'] ?? 0) / $totalLeads) * 100) : 0;
                                @endphp
                                <div class="small text-success">
                                    <i class="fas fa-percentage me-1"></i> {{ $percentage }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-success bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-check-circle fa-lg text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- In Progress -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-4 border-warning shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">In Progress</div>
                            <div class="d-flex align-items-center">
                                @php
                                    $inProgressCount = ($statusCounts['survey_done'] ?? 0) + ($statusCounts['need_data_match'] ?? 0);
                                    $percentage = $totalLeads > 0 ? round(($inProgressCount / $totalLeads) * 100) : 0;
                                @endphp
                                <div class="h3 mb-0 fw-bold text-dark me-2">{{ $inProgressCount }}</div>
                                <div class="small text-warning">
                                    <i class="fas fa-percentage me-1"></i> {{ $percentage }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-warning bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-hourglass-half fa-lg text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- On Hold -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-4 border-danger shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">On Hold</div>
                            <div class="d-flex align-items-center">
                                @php
                                    $percentage = $totalLeads > 0 ? round((($statusCounts['hold'] ?? 0) / $totalLeads) * 100) : 0;
                                @endphp
                                <div class="h3 mb-0 fw-bold text-dark me-2">{{ $statusCounts['hold'] ?? 0 }}</div>
                                <div class="small text-danger">
                                    <i class="fas fa-percentage me-1"></i> {{ $percentage }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-danger bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-pause-circle fa-lg text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Leads by Status -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tag me-2"></i> Leads by Status
                    </h6>
                    <a href="{{ route('leads.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th class="text-nowrap">Status</th>
                                                    <th class="text-center">Count</th>
                                                    <th class="text-center">Distribution</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(\App\Enums\LeadStatus::cases() as $status)
                                                    @php 
                                                        $count = $statusCounts[$status->value] ?? 0;
                                                        $percentage = $totalLeads > 0 ? round(($count / $totalLeads) * 100) : 0;
                                                        
                                                        // Determine color based on status
                                                        $color = match($status->value) {
                                                            'installed' => 'success',
                                                            'survey_done' => 'info',
                                                            'need_data_match' => 'warning',
                                                            'hold' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-light text-dark border">{{ $status->label() }}</span>
                                                        </td>
                                                        <td class="text-center fw-bold">{{ $count }}</td>
                                                        <td>
                                                            <div class="progress" style="height: 10px;">
                                                                <div class="progress-bar bg-{{ $color }}" role="progressbar" 
                                                                    style="width: {{ $percentage }}%;" 
                                                                    aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">{{ $percentage }}%</small>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('leads.index', ['status' => $status->value]) }}" 
                                                                class="btn btn-sm btn-primary">
                                                                <i class="fas fa-eye me-1"></i> View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <!-- Leads by Team -->
                        <div class="col-lg-12">
                            <div class="card shadow">
                                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-project-diagram me-2"></i> Leads by Team
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach(\App\Enums\LeadStage::cases() as $team)
                                            @php 
                                                $count = $stageCounts[$team->value] ?? 0;
                                                $percentage = $totalLeads > 0 ? round(($count / $totalLeads) * 100) : 0;
                                                
                                                // Determine color based on team
                                                $color = match($team->value) {
                                                    'radiance_team' => 'primary',
                                                    'rishi_submission' => 'success',
                                                    'unknown' => 'warning',
                                                    default => 'secondary'
                                                };
                                                
                                                $iconClass = match($team->value) {
                                                    'radiance_team' => 'fa-users',
                                                    'rishi_submission' => 'fa-paper-plane',
                                                    'unknown' => 'fa-question-circle',
                                                    default => 'fa-circle'
                                                };
                                            @endphp
                                            <div class="col-md-4 mb-4">
                                                <div class="card shadow-sm border-left-{{ $color }} h-100">
                                                    <div class="card-body">
                                                        <div class="row align-items-center">
                                                            <div class="col-auto">
                                                                <div class="icon-circle bg-{{ $color }} bg-opacity-25 p-3 rounded-circle">
                                                                    <i class="fas {{ $iconClass }} text-{{ $color }} fa-lg"></i>
                                                                </div>
                                                            </div>
                                                            <div class="col">
                                                                <h6 class="font-weight-bold text-{{ $color }} mb-1">{{ $team->name }}</h6>
                                                                <div class="h4 mb-0 font-weight-bold">{{ $count }}</div>
                                                                <div class="progress mt-2" style="height: 6px;">
                                                                    <div class="progress-bar bg-{{ $color }}" role="progressbar" 
                                                                        style="width: {{ $percentage }}%;" 
                                                                        aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                                                    </div>
                                                                </div>
                                                                <small class="text-muted">{{ $percentage }}% of total leads</small>
                                                            </div>
                                                        </div>
                                                        <div class="text-end mt-3">
                                                            <a href="{{ route('leads.index', ['stage' => $team->value]) }}" 
                                                                class="btn btn-sm btn-{{ $color }}">
                                                                <i class="fas fa-eye me-1"></i> View Leads
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Leads -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card shadow">
                                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-clock me-2"></i> Recent Leads
                                    </h6>
                                    <a href="{{ route('leads.index') }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-list-ul me-1"></i> View All Leads
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Contact</th>
                                                    <th class="text-center">Status</th>
                                                    <th class="text-center">Team</th>
                                                    <th class="text-center">Created</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($recentLeads as $lead)
                                                    @php
                                                        $statusColor = match($lead->status->value) {
                                                            'installed' => 'success',
                                                            'survey_done' => 'info',
                                                            'need_data_match' => 'warning',
                                                            'hold' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                        
                                                        $stageColor = match($lead->stage->value) {
                                                            'radiance_team' => 'primary',
                                                            'rishi_submission' => 'success',
                                                            'unknown' => 'warning',
                                                            default => 'secondary'
                                                        };
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-circle text-white bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                                    style="width: 36px; height: 36px;">
                                                                    <span>{{ strtoupper(substr($lead->first_name, 0, 1) . substr($lead->last_name, 0, 1)) }}</span>
                                                                </div>
                                                                <div>
                                                                    <div class="fw-bold">{{ $lead->first_name }} {{ $lead->last_name }}</div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div><i class="fas fa-envelope text-muted me-1"></i> {{ $lead->email }}</div>
                                                            <div><i class="fas fa-phone text-muted me-1"></i> {{ $lead->phone }}</div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-{{ $statusColor }}">{{ $lead->status->label() }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-{{ $stageColor }}">{{ $lead->stage->label() }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div>{{ $lead->created_at->format('M d, Y') }}</div>
                                                            <small class="text-muted">{{ $lead->created_at->diffForHumans() }}</small>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-eye me-1"></i> Details
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-4">
                                                            <div class="text-muted mb-2">
                                                                <i class="fas fa-info-circle fa-2x"></i>
                                                            </div>
                                                            <div class="h6 text-muted">No recent leads found.</div>
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
@endsection