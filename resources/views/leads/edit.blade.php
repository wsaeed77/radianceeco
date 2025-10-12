@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Edit Lead') }}</span>
                    <div>
                        <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-info me-2">View Lead</a>
                        <a href="{{ route('leads.index') }}" class="btn btn-sm btn-secondary">Back to Leads</a>
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

                    <form method="POST" action="{{ route('leads.update', $lead->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        Lead Information
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name', $lead->first_name) }}" required>
                                                @error('first_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name', $lead->last_name) }}" required>
                                                @error('last_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="email" class="form-label">Email Address</label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $lead->email) }}">
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="phone" class="form-label">Phone Number</label>
                                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $lead->phone) }}">
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="address_line_1" class="form-label">Address Line 1</label>
                                            <input type="text" class="form-control @error('address_line_1') is-invalid @enderror" id="address_line_1" name="address_line_1" value="{{ old('address_line_1', $lead->address_line_1) }}">
                                            @error('address_line_1')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="address_line_2" class="form-label">Address Line 2</label>
                                            <input type="text" class="form-control @error('address_line_2') is-invalid @enderror" id="address_line_2" name="address_line_2" value="{{ old('address_line_2', $lead->address_line_2) }}">
                                            @error('address_line_2')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="city" class="form-label">City</label>
                                                <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $lead->city) }}">
                                                @error('city')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="zip_code" class="form-label">Zip Code</label>
                                                <input type="text" class="form-control @error('zip_code') is-invalid @enderror" id="zip_code" name="zip_code" value="{{ old('zip_code', $lead->zip_code) }}">
                                                @error('zip_code')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-info text-white">
                                        Lead Status and Source
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                                    <option value="">Select Status</option>
                                                    @foreach($statuses as $status)
                                                        <option value="{{ $status->value }}" {{ old('status', $lead->status->value) == $status->value ? 'selected' : '' }}>
                                                            {{ $status->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="stage" class="form-label">Team <span class="text-danger">*</span></label>
                                                <select class="form-select @error('stage') is-invalid @enderror" id="stage" name="stage" required>
                                                    <option value="">Select Team</option>
                                                    @foreach($stages as $team)
                                                        <option value="{{ $team->value }}" {{ old('stage', $lead->stage->value) == $team->value ? 'selected' : '' }}>
                                                            {{ $team->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('stage')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="source" class="form-label">Source</label>
                                            <select class="form-select @error('source') is-invalid @enderror" id="source" name="source">
                                                <option value="">Select a Source</option>
                                                @foreach(\App\Enums\LeadSource::cases() as $source)
                                                    <option value="{{ $source->value }}" {{ old('source', $lead->source?->value) == $source->value ? 'selected' : '' }}>{{ $source->value }}</option>
                                                @endforeach
                                            </select>
                                            @error('source')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="source_details" class="form-label">Source Details</label>
                                            <input type="text" class="form-control @error('source_details') is-invalid @enderror" id="source_details" name="source_details" value="{{ old('source_details', $lead->source_details) }}">
                                            @error('source_details')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                            
                                        <div class="mb-3">
                                            <label for="grant_type" class="form-label">Grant Type</label>
                                            <select class="form-select @error('grant_type') is-invalid @enderror" id="grant_type" name="grant_type">
                                                <option value="">Select Grant Type</option>
                                                <option value="GBIS" {{ old('grant_type', $lead->grant_type) == 'GBIS' ? 'selected' : '' }}>GBIS</option>
                                                <option value="ECO4" {{ old('grant_type', $lead->grant_type) == 'ECO4' ? 'selected' : '' }}>ECO4</option>
                                            </select>
                                            @error('grant_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="notes" class="form-label">Notes</label>
                                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="5">{{ old('notes', $lead->notes) }}</textarea>
                                            @error('notes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="agent_id" class="form-label">Assigned Agent</label>
                                            <select class="form-select @error('agent_id') is-invalid @enderror" id="agent_id" name="agent_id">
                                                <option value="">Not Assigned</option>
                                                @foreach($agents as $agent)
                                                    <option value="{{ $agent->id }}" {{ (old('agent_id', $lead->agent_id) == $agent->id) ? 'selected' : '' }}>
                                                        {{ $agent->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('agent_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Data Match Section -->
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header bg-purple text-white">
                                    Data Match
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="benefit_holder_name" class="form-label">Benefit Holder Name</label>
                                            <input type="text" class="form-control @error('benefit_holder_name') is-invalid @enderror" id="benefit_holder_name" name="benefit_holder_name" value="{{ old('benefit_holder_name', $lead->benefit_holder_name) }}">
                                            @error('benefit_holder_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="benefit_holder_dob" class="form-label">Benefit Holder DOB</label>
                                            <input type="date" class="form-control @error('benefit_holder_dob') is-invalid @enderror" id="benefit_holder_dob" name="benefit_holder_dob" value="{{ old('benefit_holder_dob', $lead->benefit_holder_dob ? $lead->benefit_holder_dob->format('Y-m-d') : '') }}">
                                            @error('benefit_holder_dob')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="data_match_status" class="form-label">Data Match Status</label>
                                            <select class="form-select @error('data_match_status') is-invalid @enderror" id="data_match_status" name="data_match_status">
                                                <option value="">Select Status</option>
                                                <option value="Pending" {{ old('data_match_status', $lead->data_match_status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="Sent" {{ old('data_match_status', $lead->data_match_status) == 'Sent' ? 'selected' : '' }}>Sent</option>
                                                <option value="Matched" {{ old('data_match_status', $lead->data_match_status) == 'Matched' ? 'selected' : '' }}>Matched</option>
                                                <option value="Unmatched" {{ old('data_match_status', $lead->data_match_status) == 'Unmatched' ? 'selected' : '' }}>Unmatched</option>
                                                <option value="Unverified" {{ old('data_match_status', $lead->data_match_status) == 'Unverified' ? 'selected' : '' }}>Unverified</option>
                                            </select>
                                            @error('data_match_status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Multiple Phone Numbers</label>
                                            <div id="phone-numbers-container">
                                                @php
                                                    $phoneNumbers = old('multi_phone_numbers', $lead->multi_phone_numbers ?? []);
                                                    if (!is_array($phoneNumbers)) {
                                                        $phoneNumbers = [];
                                                    }
                                                @endphp
                                                
                                                @if(count($phoneNumbers) > 0)
                                                    @foreach($phoneNumbers as $index => $phoneNumber)
                                                        <div class="input-group mb-2">
                                                            <input type="text" class="form-control phone-label" name="multi_phone_labels[]" placeholder="Label" value="{{ $phoneNumber['label'] ?? '' }}">
                                                            <input type="text" class="form-control phone-number" name="multi_phone_numbers[]" placeholder="Phone Number" value="{{ $phoneNumber['number'] ?? '' }}">
                                                            <button type="button" class="btn btn-danger remove-phone">Remove</button>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="input-group mb-2">
                                                        <input type="text" class="form-control phone-label" name="multi_phone_labels[]" placeholder="Label">
                                                        <input type="text" class="form-control phone-number" name="multi_phone_numbers[]" placeholder="Phone Number">
                                                        <button type="button" class="btn btn-danger remove-phone">Remove</button>
                                                    </div>
                                                @endif
                                            </div>
                                            <button type="button" class="btn btn-success btn-sm mt-2" id="add-phone-btn">Add Another Phone</button>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <label for="data_match_remarks" class="form-label">Data Match Remarks</label>
                                            <textarea class="form-control @error('data_match_remarks') is-invalid @enderror" id="data_match_remarks" name="data_match_remarks" rows="3">{{ old('data_match_remarks', $lead->data_match_remarks) }}</textarea>
                                            @error('data_match_remarks')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Eligibility Details -->
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    Eligibility Details
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="occupancy_type" class="form-label">Occupancy Type</label>
                                            <select class="form-select @error('occupancy_type') is-invalid @enderror" id="occupancy_type" name="occupancy_type">
                                                <option value="">Select Occupancy Type</option>
                                                <option value="owner" {{ old('occupancy_type', $lead->occupancy_type) == 'owner' ? 'selected' : '' }}>Owner</option>
                                                <option value="tenant" {{ old('occupancy_type', $lead->occupancy_type) == 'tenant' ? 'selected' : '' }}>Tenant</option>
                                            </select>
                                            @error('occupancy_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="eligibility_client_dob" class="form-label">Client DOB</label>
                                            <input type="date" class="form-control @error('eligibility_client_dob') is-invalid @enderror" id="eligibility_client_dob" name="eligibility_client_dob" value="{{ old('eligibility_client_dob', $lead->eligibility_client_dob ? $lead->eligibility_client_dob->format('Y-m-d') : '') }}">
                                            @error('eligibility_client_dob')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="possible_grant_types" class="form-label">Possible Grant</label>
                                            <select class="form-select @error('possible_grant_types') is-invalid @enderror" id="possible_grant_types" name="possible_grant_types">
                                                <option value="">Select Possible Grant</option>
                                                <option value="Loft only" {{ old('possible_grant_types', $lead->possible_grant_types) == 'Loft only' ? 'selected' : '' }}>Loft only</option>
                                                <option value="Loft+TRV+Thermostate" {{ old('possible_grant_types', $lead->possible_grant_types) == 'Loft+TRV+Thermostate' ? 'selected' : '' }}>Loft+TRV+Thermostate</option>
                                                <option value="Boiler" {{ old('possible_grant_types', $lead->possible_grant_types) == 'Boiler' ? 'selected' : '' }}>Boiler</option>
                                                <option value="Boiler+Loft" {{ old('possible_grant_types', $lead->possible_grant_types) == 'Boiler+Loft' ? 'selected' : '' }}>Boiler+Loft</option>
                                            </select>
                                            @error('possible_grant_types')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="benefit_type" class="form-label">Benefit</label>
                                            <select class="form-select @error('benefit_type') is-invalid @enderror" id="benefit_type" name="benefit_type">
                                                <option value="">Select Benefit</option>
                                                <option value="Universal Credit" {{ old('benefit_type', $lead->benefit_type) == 'Universal Credit' ? 'selected' : '' }}>Universal Credit</option>
                                                <option value="Child Benefit" {{ old('benefit_type', $lead->benefit_type) == 'Child Benefit' ? 'selected' : '' }}>Child Benefit</option>
                                                <option value="Pension Credit" {{ old('benefit_type', $lead->benefit_type) == 'Pension Credit' ? 'selected' : '' }}>Pension Credit</option>
                                                <option value="Child Tax Credit" {{ old('benefit_type', $lead->benefit_type) == 'Child Tax Credit' ? 'selected' : '' }}>Child Tax Credit</option>
                                                <option value="Income Support" {{ old('benefit_type', $lead->benefit_type) == 'Income Support' ? 'selected' : '' }}>Income Support</option>
                                                <option value="Job Seeker Allowance" {{ old('benefit_type', $lead->benefit_type) == 'Job Seeker Allowance' ? 'selected' : '' }}>Job Seeker Allowance</option>
                                                <option value="No Benefit" {{ old('benefit_type', $lead->benefit_type) == 'No Benefit' ? 'selected' : '' }}>No Benefit</option>
                                            </select>
                                            @error('benefit_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="council_tax_band" class="form-label">Council Tax Band</label>
                                            <select class="form-select @error('council_tax_band') is-invalid @enderror" id="council_tax_band" name="council_tax_band">
                                                <option value="">Select Council Tax Band</option>
                                                <option value="A" {{ old('council_tax_band', $lead->council_tax_band) == 'A' ? 'selected' : '' }}>Band A</option>
                                                <option value="B" {{ old('council_tax_band', $lead->council_tax_band) == 'B' ? 'selected' : '' }}>Band B</option>
                                                <option value="C" {{ old('council_tax_band', $lead->council_tax_band) == 'C' ? 'selected' : '' }}>Band C</option>
                                                <option value="D" {{ old('council_tax_band', $lead->council_tax_band) == 'D' ? 'selected' : '' }}>Band D</option>
                                                <option value="E" {{ old('council_tax_band', $lead->council_tax_band) == 'E' ? 'selected' : '' }}>Band E</option>
                                                <option value="F" {{ old('council_tax_band', $lead->council_tax_band) == 'F' ? 'selected' : '' }}>Band F</option>
                                            </select>
                                            @error('council_tax_band')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="epc_rating" class="form-label">EPC Rating</label>
                                            <select class="form-select @error('epc_rating') is-invalid @enderror" id="epc_rating" name="epc_rating">
                                                <option value="">Select EPC Rating</option>
                                                <option value="A" {{ old('epc_rating', $lead->epc_rating) == 'A' ? 'selected' : '' }}>A</option>
                                                <option value="B" {{ old('epc_rating', $lead->epc_rating) == 'B' ? 'selected' : '' }}>B</option>
                                                <option value="C" {{ old('epc_rating', $lead->epc_rating) == 'C' ? 'selected' : '' }}>C</option>
                                                <option value="D" {{ old('epc_rating', $lead->epc_rating) == 'D' ? 'selected' : '' }}>D</option>
                                                <option value="E" {{ old('epc_rating', $lead->epc_rating) == 'E' ? 'selected' : '' }}>E</option>
                                                <option value="F" {{ old('epc_rating', $lead->epc_rating) == 'F' ? 'selected' : '' }}>F</option>
                                            </select>
                                            @error('epc_rating')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="gas_safe_info" class="form-label">GAS SAFE</label>
                                            <input type="text" class="form-control @error('gas_safe_info') is-invalid @enderror" id="gas_safe_info" name="gas_safe_info" value="{{ old('gas_safe_info', $lead->gas_safe_info) }}">
                                            @error('gas_safe_info')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="poa_info" class="form-label">Proof of Address (POA)</label>
                                            <input type="text" class="form-control @error('poa_info') is-invalid @enderror" id="poa_info" name="poa_info" value="{{ old('poa_info', $lead->poa_info) }}">
                                            @error('poa_info')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="epc_details" class="form-label">EPC Details</label>
                                            <textarea class="form-control @error('epc_details') is-invalid @enderror" id="epc_details" name="epc_details" rows="3">{{ old('epc_details', $lead->epc_details) }}</textarea>
                                            @error('epc_details')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary">Update Lead</button>
                                <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-secondary ms-2">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const phoneContainer = document.getElementById('phone-numbers-container');
        const addPhoneBtn = document.getElementById('add-phone-btn');
        
        // Add phone number field
        addPhoneBtn.addEventListener('click', function() {
            const phoneGroup = document.createElement('div');
            phoneGroup.className = 'input-group mb-2';
            
            phoneGroup.innerHTML = `
                <input type="text" class="form-control phone-label" name="multi_phone_labels[]" placeholder="Label">
                <input type="text" class="form-control phone-number" name="multi_phone_numbers[]" placeholder="Phone Number">
                <button type="button" class="btn btn-danger remove-phone">Remove</button>
            `;
            
            phoneContainer.appendChild(phoneGroup);
            
            // Add event listener to the new remove button
            phoneGroup.querySelector('.remove-phone').addEventListener('click', function() {
                phoneContainer.removeChild(phoneGroup);
            });
        });
        
        // Remove phone number field
        document.querySelectorAll('.remove-phone').forEach(button => {
            button.addEventListener('click', function() {
                const phoneGroup = this.parentElement;
                phoneContainer.removeChild(phoneGroup);
            });
        });
    });
</script>
@endpush

@endsection