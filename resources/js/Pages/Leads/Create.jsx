import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Card, { CardHeader, CardContent } from '@/Components/Card';
import FormInput from '@/Components/FormInput';
import FormSelect from '@/Components/FormSelect';
import FormTextarea from '@/Components/FormTextarea';
import Button from '@/Components/Button';
import Alert from '@/Components/Alert';

export default function CreateLead({ sources, stages, statuses, agents }) {
    const { data, setData, post, processing, errors } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address_line_1: '',
        address_line_2: '',
        city: '',
        assigned_to: '',
        zip_code: '',
        status: '',
        stage: '',
        source: '',
        source_details: '',
        grant_type: '',
        notes: '',
        agent_id: '',
        // Data Match fields
        benefit_holder_name: '',
        benefit_holder_dob: '',
        data_match_status: '',
        data_match_remarks: '',
        multi_phone_labels: [''],
        multi_phone_numbers: [''],
        // Eligibility fields
        occupancy_type: '',
        eligibility_client_dob: '',
        possible_grant_types: '',
        benefit_type: '',
        council_tax_band: '',
        floor_area: '',
        epc_rating: '',
        gas_safe_info: '',
        poa_info: '',
        epc_details: '',
    });

    const [phoneFields, setPhoneFields] = useState([{ label: '', number: '' }]);

    const addPhoneField = () => {
        setPhoneFields([...phoneFields, { label: '', number: '' }]);
    };

    const removePhoneField = (index) => {
        const newPhones = phoneFields.filter((_, i) => i !== index);
        setPhoneFields(newPhones);
        setData({
            ...data,
            multi_phone_labels: newPhones.map(p => p.label),
            multi_phone_numbers: newPhones.map(p => p.number),
        });
    };

    const updatePhoneField = (index, field, value) => {
        const newPhones = [...phoneFields];
        newPhones[index][field] = value;
        setPhoneFields(newPhones);
        setData({
            ...data,
            multi_phone_labels: newPhones.map(p => p.label),
            multi_phone_numbers: newPhones.map(p => p.number),
        });
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('leads.store'));
    };

    return (
        <AppLayout>
            <Head title="Create New Lead" />

            <div className="max-w-7xl mx-auto">
                <Card padding={false} className="mb-6">
                    <CardHeader>
                        <div className="flex justify-between items-center">
                            <h2 className="text-xl font-semibold">Create New Lead</h2>
                            <Link href={route('leads.index')}>
                                <Button variant="secondary" size="sm">Back to Leads</Button>
                            </Link>
                        </div>
                    </CardHeader>
                </Card>

                {Object.keys(errors).length > 0 && (
                    <Alert type="error" message="Please fix the errors below" className="mb-6" />
                )}

                <form onSubmit={submit}>
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        {/* Lead Information */}
                        <Card padding={false}>
                            <CardHeader className="bg-primary-600">
                                <h3 className="text-lg font-semibold text-white">Lead Information</h3>
                            </CardHeader>
                            <CardContent className="p-6">
                                <div className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <FormInput
                                            label="First Name"
                                            value={data.first_name}
                                            onChange={(e) => setData('first_name', e.target.value)}
                                            error={errors.first_name}
                                            required={false}
                                        />
                                        <FormInput
                                            label="Last Name"
                                            value={data.last_name}
                                            onChange={(e) => setData('last_name', e.target.value)}
                                            error={errors.last_name}
                                            required={false}
                                        />
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <FormInput
                                            label="Email Address"
                                            type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            error={errors.email}
                                        />
                                        <FormInput
                                            label="Phone Number"
                                            value={data.phone}
                                            onChange={(e) => setData('phone', e.target.value)}
                                            error={errors.phone}
                                        />
                                    </div>

                                    <FormInput
                                        label="Address Line 1"
                                        value={data.address_line_1}
                                        onChange={(e) => setData('address_line_1', e.target.value)}
                                        error={errors.address_line_1}
                                    />

                                    <FormInput
                                        label="Address Line 2"
                                        value={data.address_line_2}
                                        onChange={(e) => setData('address_line_2', e.target.value)}
                                        error={errors.address_line_2}
                                    />

                                    <div className="grid grid-cols-2 gap-4">
                                        <FormInput
                                            label="City"
                                            value={data.city}
                                            onChange={(e) => setData('city', e.target.value)}
                                            error={errors.city}
                                        />
                                        <FormInput
                                            label="Postcode"
                                            value={data.zip_code}
                                            onChange={(e) => setData('zip_code', e.target.value)}
                                            error={errors.zip_code}
                                            required
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Lead Status and Source */}
                        <Card padding={false}>
                            <CardHeader className="bg-blue-500">
                                <h3 className="text-lg font-semibold text-white">Lead Status and Source</h3>
                            </CardHeader>
                            <CardContent className="p-6">
                                <div className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <FormSelect
                                            label="Status"
                                            value={data.status}
                                            onChange={(e) => setData('status', e.target.value)}
                                            error={errors.status}
                                            required
                                        >
                                            <option value="">Select Status</option>
                                            {statuses?.map((status) => (
                                                <option key={status.value} value={status.value}>
                                                    {status.label}
                                                </option>
                                            ))}
                                        </FormSelect>

                                        <FormSelect
                                            label="Team"
                                            value={data.stage}
                                            onChange={(e) => setData('stage', e.target.value)}
                                            error={errors.stage}
                                            required
                                        >
                                            <option value="">Select Team</option>
                                            {stages?.map((stage) => (
                                                <option key={stage.value} value={stage.value}>
                                                    {stage.label}
                                                </option>
                                            ))}
                                        </FormSelect>
                                    </div>

                                    <FormSelect
                                        label="Source"
                                        value={data.source}
                                        onChange={(e) => setData('source', e.target.value)}
                                        error={errors.source}
                                    >
                                        <option value="">Select a Source</option>
                                        {sources?.map((source) => (
                                            <option key={source.value} value={source.value}>
                                                {source.value}
                                            </option>
                                        ))}
                                    </FormSelect>

                                    <FormInput
                                        label="Source Details"
                                        value={data.source_details}
                                        onChange={(e) => setData('source_details', e.target.value)}
                                        error={errors.source_details}
                                    />

                                    <FormSelect
                                        label="Grant Type"
                                        value={data.grant_type}
                                        onChange={(e) => setData('grant_type', e.target.value)}
                                        error={errors.grant_type}
                                    >
                                        <option value="">Select Grant Type</option>
                                        <option value="GBIS">GBIS</option>
                                        <option value="ECO4">ECO4</option>
                                    </FormSelect>

                                    <FormTextarea
                                        label="Notes"
                                        value={data.notes}
                                        onChange={(e) => setData('notes', e.target.value)}
                                        error={errors.notes}
                                        rows={5}
                                    />

                                    <FormSelect
                                        label="Assigned Agent"
                                        value={data.agent_id}
                                        onChange={(e) => setData('agent_id', e.target.value)}
                                        error={errors.agent_id}
                                    >
                                        <option value="">Not Assigned</option>
                                        {agents?.map((agent) => (
                                            <option key={agent.id} value={agent.id}>
                                                {agent.name}
                                            </option>
                                        ))}
                                    </FormSelect>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Data Match Section */}
                    <Card padding={false} className="mb-6">
                        <CardHeader className="bg-purple-600">
                            <h3 className="text-lg font-semibold text-white">Data Match</h3>
                        </CardHeader>
                        <CardContent className="p-6">
                            <div className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <FormInput
                                        label="Benefit Holder Name"
                                        value={data.benefit_holder_name}
                                        onChange={(e) => setData('benefit_holder_name', e.target.value)}
                                        error={errors.benefit_holder_name}
                                    />
                                    <FormInput
                                        label="Benefit Holder DOB"
                                        type="date"
                                        value={data.benefit_holder_dob}
                                        onChange={(e) => setData('benefit_holder_dob', e.target.value)}
                                        error={errors.benefit_holder_dob}
                                    />
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <FormSelect
                                        label="Data Match Status"
                                        value={data.data_match_status}
                                        onChange={(e) => setData('data_match_status', e.target.value)}
                                        error={errors.data_match_status}
                                    >
                                        <option value="">Select Status</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Sent">Sent</option>
                                        <option value="Matched">Matched</option>
                                        <option value="Unmatched">Unmatched</option>
                                        <option value="Unverified">Unverified</option>
                                    </FormSelect>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Multiple Phone Numbers
                                    </label>
                                    {phoneFields.map((phone, index) => (
                                        <div key={index} className="flex gap-2 mb-2">
                                            <input
                                                type="text"
                                                className="flex-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                                placeholder="Label"
                                                value={phone.label}
                                                onChange={(e) => updatePhoneField(index, 'label', e.target.value)}
                                            />
                                            <input
                                                type="text"
                                                className="flex-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                                placeholder="Phone Number"
                                                value={phone.number}
                                                onChange={(e) => updatePhoneField(index, 'number', e.target.value)}
                                            />
                                            <Button
                                                type="button"
                                                variant="danger"
                                                size="sm"
                                                onClick={() => removePhoneField(index)}
                                            >
                                                Remove
                                            </Button>
                                        </div>
                                    ))}
                                    <Button
                                        type="button"
                                        variant="success"
                                        size="sm"
                                        onClick={addPhoneField}
                                    >
                                        Add Another Phone
                                    </Button>
                                </div>

                                <FormTextarea
                                    label="Data Match Remarks"
                                    value={data.data_match_remarks}
                                    onChange={(e) => setData('data_match_remarks', e.target.value)}
                                    error={errors.data_match_remarks}
                                    rows={3}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Eligibility Details */}
                    <Card padding={false} className="mb-6">
                        <CardHeader className="bg-success-600">
                            <h3 className="text-lg font-semibold text-white">Eligibility Details</h3>
                        </CardHeader>
                        <CardContent className="p-6">
                            <div className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <FormSelect
                                        label="Occupancy Type"
                                        value={data.occupancy_type}
                                        onChange={(e) => setData('occupancy_type', e.target.value)}
                                        error={errors.occupancy_type}
                                    >
                                        <option value="">Select Occupancy Type</option>
                                        <option value="owner">Owner</option>
                                        <option value="tenant">Tenant</option>
                                    </FormSelect>
                                    <FormInput
                                        label="Client DOB"
                                        type="date"
                                        value={data.eligibility_client_dob}
                                        onChange={(e) => setData('eligibility_client_dob', e.target.value)}
                                        error={errors.eligibility_client_dob}
                                    />
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <FormSelect
                                        label="Possible Grant"
                                        value={data.possible_grant_types}
                                        onChange={(e) => setData('possible_grant_types', e.target.value)}
                                        error={errors.possible_grant_types}
                                    >
                                        <option value="">Select Possible Grant</option>
                                        <option value="Loft only">Loft only</option>
                                        <option value="Loft+TRV+Thermostate">Loft+TRV+Thermostate</option>
                                        <option value="Boiler">Boiler</option>
                                        <option value="Boiler+Loft">Boiler+Loft</option>
                                    </FormSelect>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <FormSelect
                                        label="Benefit"
                                        value={data.benefit_type}
                                        onChange={(e) => setData('benefit_type', e.target.value)}
                                        error={errors.benefit_type}
                                    >
                                        <option value="">Select Benefit</option>
                                        <option value="Universal Credit">Universal Credit</option>
                                        <option value="Child Benefit">Child Benefit</option>
                                        <option value="Pension Credit">Pension Credit</option>
                                        <option value="Child Tax Credit">Child Tax Credit</option>
                                        <option value="Income Support">Income Support</option>
                                        <option value="Job Seeker Allowance">Job Seeker Allowance</option>
                                        <option value="No Benefit">No Benefit</option>
                                    </FormSelect>
                                    <FormSelect
                                        label="Council Tax Band"
                                        value={data.council_tax_band}
                                        onChange={(e) => setData('council_tax_band', e.target.value)}
                                        error={errors.council_tax_band}
                                    >
                                        <option value="">Select Council Tax Band</option>
                                        <option value="A">Band A</option>
                                        <option value="B">Band B</option>
                                        <option value="C">Band C</option>
                                        <option value="D">Band D</option>
                                        <option value="E">Band E</option>
                                        <option value="F">Band F</option>
                                    </FormSelect>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <FormInput
                                        label="Floor Area (m²)"
                                        value={data.floor_area}
                                        onChange={(e) => setData('floor_area', e.target.value)}
                                        error={errors.floor_area}
                                        placeholder="e.g., 85"
                                    />
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <FormSelect
                                        label="EPC Rating"
                                        value={data.epc_rating}
                                        onChange={(e) => setData('epc_rating', e.target.value)}
                                        error={errors.epc_rating}
                                    >
                                        <option value="">Select EPC Rating</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                        <option value="E">E</option>
                                        <option value="F">F</option>
                                    </FormSelect>
                                    <FormInput
                                        label="GAS SAFE"
                                        value={data.gas_safe_info}
                                        onChange={(e) => setData('gas_safe_info', e.target.value)}
                                        error={errors.gas_safe_info}
                                    />
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <FormInput
                                        label="Proof of Address (POA)"
                                        value={data.poa_info}
                                        onChange={(e) => setData('poa_info', e.target.value)}
                                        error={errors.poa_info}
                                    />
                                    <FormTextarea
                                        label="EPC Details"
                                        value={data.epc_details}
                                        onChange={(e) => setData('epc_details', e.target.value)}
                                        error={errors.epc_details}
                                        rows={3}
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Form Actions */}
                    <Card className="mt-6">
                        <div className="flex justify-center gap-4">
                            <Button type="submit" variant="primary" disabled={processing}>
                                {processing ? 'Creating...' : 'Create Lead'}
                            </Button>
                            <Link href={route('leads.index')}>
                                <Button type="button" variant="secondary">Cancel</Button>
                            </Link>
                        </div>
                    </Card>
                </form>
            </div>
        </AppLayout>
    );
}

