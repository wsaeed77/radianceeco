@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Upload Document') }}</span>
                    <div>
                        <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-secondary">Back to Lead</a>
                    </div>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                        @isset($activity)
                            <input type="hidden" name="activity_id" value="{{ $activity->id }}">
                        @endisset

                        <div class="mb-3">
                            <label for="kind" class="form-label">Document Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('kind') is-invalid @enderror" id="kind" name="kind" required>
                                <option value="">Select Document Type</option>
                                @foreach($documentKinds as $documentKind)
                                    <option value="{{ $documentKind->value }}">
                                        {{ $documentKind->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kind')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="document" class="form-label">File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('document') is-invalid @enderror" id="document" name="document" required>
                            <div class="form-text">Maximum file size: 10MB</div>
                            @error('document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Upload Document</button>
                            <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection