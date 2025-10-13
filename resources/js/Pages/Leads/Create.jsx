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
        status: 'new',
        stage: 'radiance_team',
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
        // Additional Information
        epr_report: '',
        // EPR Fields
        epr_measures: [],
        epr_pre_rating: '',
        epr_post_rating: '',
        epr_abs: '',
        epr_amount_funded: '',
        epr_payments: [],
    });

    const [phoneFields, setPhoneFields] = useState([{ label: '', number: '' }]);
    const [eprPayments, setEprPayments] = useState([]);

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

    // EPR Payment handlers
    const addEprPayment = () => {
        const newPayment = { type: '', amount: '', quantity: '', rate: '', percentage: '' };
        setEprPayments([...eprPayments, newPayment]);
        setData('epr_payments', [...eprPayments, newPayment]);
    };

    const removeEprPayment = (index) => {
        const newPayments = eprPayments.filter((_, i) => i !== index);
        setEprPayments(newPayments);
        setData('epr_payments', newPayments);
    };

    const updateEprPayment = (index, field, value) => {
        const newPayments = [...eprPayments];
        newPayments[index][field] = value;
        
        // Calculate total for TRV/TTZC
        if (newPayments[index].type === 'TRV/TTZC') {
            const qty = parseFloat(newPayments[index].quantity) || 0;
            const rate = parseFloat(newPayments[index].rate) || 0;
            newPayments[index].amount = (qty * rate).toFixed(2);
        }
        
        // Calculate VAT based on percentage of other expenses
        if (newPayments[index].type === 'VAT') {
            const percentage = parseFloat(newPayments[index].percentage) || 0;
            // Calculate total of all non-VAT expenses
            const otherExpenses = newPayments
                .filter((p, i) => i !== index && p.type !== 'VAT')
                .reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
            newPayments[index].amount = ((otherExpenses * percentage) / 100).toFixed(2);
        }
        
        setEprPayments(newPayments);
        setData('epr_payments', newPayments);
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

                    {/* EPR Section */}
                    <Card padding={false} className="mb-6">
                        <CardHeader className="bg-indigo-600">
                            <h3 className="text-lg font-semibold text-white">PR (Energy Performance Report) and Submission</h3>
                        </CardHeader>
                        <CardContent className="p-6">
                            <div className="space-y-4">
                                {/* Measures */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Measures
                                    </label>
                                    <div className="space-y-2">
                                        {['Loft Insulation', 'Smart Thermostat', 'TRV', 'Programmer and Room Thermostat'].map((measure) => (
                                            <label key={measure} className="flex items-center">
                                                <input
                                                    type="checkbox"
                                                    checked={data.epr_measures?.includes(measure)}
                                                    onChange={(e) => {
                                                        const measures = data.epr_measures || [];
                                                        if (e.target.checked) {
                                                            setData('epr_measures', [...measures, measure]);
                                                        } else {
                                                            setData('epr_measures', measures.filter(m => m !== measure));
                                                        }
                                                    }}
                                                    className="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                />
                                                <span className="ml-2 text-sm text-gray-700">{measure}</span>
                                            </label>
                                        ))}
                                    </div>
                                </div>

                                {/* EPR Report Status */}
                                <div>
                                    <FormSelect
                                        label="EPR Report Status"
                                        value={data.epr_report}
                                        onChange={(e) => setData('epr_report', e.target.value)}
                                        error={errors.epr_report}
                                    >
                                        <option value="">Select Status</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Under Process">Under Process</option>
                                        <option value="Pending">Pending</option>
                                    </FormSelect>
                                </div>

                                {/* Pre and Post Rating */}
                                <div className="grid grid-cols-2 gap-4">
                                    <FormSelect
                                        label="Pre Rating (Before)"
                                        value={data.epr_pre_rating}
                                        onChange={(e) => setData('epr_pre_rating', e.target.value)}
                                        error={errors.epr_pre_rating}
                                    >
                                        <option value="">Select Pre Rating</option>
                                        <option value="21 (Low E)">21 (Low E)</option>
                                        <option value="39 (High E)">39 (High E)</option>
                                        <option value="55 (Low D)">55 (Low D)</option>
                                        <option value="68 (High D)">68 (High D)</option>
                                        <option value="69 (Low C)">69 (Low C)</option>
                                        <option value="80 (High C)">80 (High C)</option>
                                        <option value="81 (B)">81 (B)</option>
                                        <option value="92 (A)">92 (A)</option>
                                    </FormSelect>
                                    <FormSelect
                                        label="Post Rating (After)"
                                        value={data.epr_post_rating}
                                        onChange={(e) => setData('epr_post_rating', e.target.value)}
                                        error={errors.epr_post_rating}
                                    >
                                        <option value="">Select Post Rating</option>
                                        <option value="21 (Low E)">21 (Low E)</option>
                                        <option value="39 (High E)">39 (High E)</option>
                                        <option value="55 (Low D)">55 (Low D)</option>
                                        <option value="68 (High D)">68 (High D)</option>
                                        <option value="69 (Low C)">69 (Low C)</option>
                                        <option value="80 (High C)">80 (High C)</option>
                                        <option value="81 (B)">81 (B)</option>
                                        <option value="92 (A)">92 (A)</option>
                                    </FormSelect>
                                </div>

                                {/* ABS and Amount Funded */}
                                <div className="grid grid-cols-2 gap-4">
                                    <FormInput
                                        label="ABS"
                                        type="number"
                                        step="0.01"
                                        value={data.epr_abs}
                                        onChange={(e) => setData('epr_abs', e.target.value)}
                                        error={errors.epr_abs}
                                        placeholder="0.00"
                                    />
                                    <FormInput
                                        label="Amount Funded"
                                        type="number"
                                        step="0.01"
                                        value={data.epr_amount_funded}
                                        onChange={(e) => setData('epr_amount_funded', e.target.value)}
                                        error={errors.epr_amount_funded}
                                        placeholder="0.00"
                                    />
                                </div>

                                {/* Expenses */}
                                <div className="mt-6">
                                    <div className="flex items-center justify-between mb-4">
                                        <h4 className="text-sm font-semibold text-gray-900">Expenses</h4>
                                        <Button type="button" variant="secondary" size="sm" onClick={addEprPayment}>
                                            Add Expense
                                        </Button>
                                    </div>

                                    {eprPayments.length > 0 ? (
                                        <div className="space-y-4">
                                            {eprPayments.map((payment, index) => (
                                                <div key={index} className="p-4 border border-gray-200 rounded-lg bg-gray-50">
                                                    <div className="grid grid-cols-12 gap-3">
                                                        <div className="col-span-11">
                                                            <div className="grid grid-cols-1 md:grid-cols-4 gap-3">
                                                                <FormSelect
                                                                    label="Payment Type"
                                                                    value={payment.type}
                                                                    onChange={(e) => updateEprPayment(index, 'type', e.target.value)}
                                                                >
                                                                    <option value="">Select Type</option>
                                                                    <option value="Early Fee">Early Fee</option>
                                                                    <option value="C3">C3</option>
                                                                    <option value="Gas Engineer">Gas Engineer</option>
                                                                    <option value="Remedial">Remedial</option>
                                                                    <option value="Loft Material">Loft Material</option>
                                                                    <option value="Loft Labour">Loft Labour</option>
                                                                    <option value="Extractor Fan">Extractor Fan</option>
                                                                    <option value="Trickle Vents">Trickle Vents</option>
                                                                    <option value="Boiler Material">Boiler Material</option>
                                                                    <option value="ESI">ESI</option>
                                                                    <option value="Secondary Heating">Secondary Heating</option>
                                                                    <option value="Data Match">Data Match</option>
                                                                    <option value="Coordination">Coordination</option>
                                                                    <option value="GDGC">GDGC</option>
                                                                    <option value="Land Registry">Land Registry</option>
                                                                    <option value="Administrative Charges">Administrative Charges</option>
                                                                    <option value="Surveyor">Surveyor</option>
                                                                    <option value="Misc">Misc</option>
                                                                    <option value="TRV/TTZC">TRV/TTZC</option>
                                                                    <option value="VAT">VAT</option>
                                                                </FormSelect>

                                                                {payment.type === 'VAT' ? (
                                                                    <>
                                                                        <FormInput
                                                                            label="Percentage (%)"
                                                                            type="number"
                                                                            step="0.01"
                                                                            value={payment.percentage}
                                                                            onChange={(e) => updateEprPayment(index, 'percentage', e.target.value)}
                                                                            placeholder="20"
                                                                        />
                                                                        <div className="col-span-2">
                                                                            <FormInput
                                                                                label="VAT Amount"
                                                                                type="number"
                                                                                value={payment.amount}
                                                                                disabled
                                                                                className="bg-gray-100"
                                                                            />
                                                                        </div>
                                                                    </>
                                                                ) : payment.type === 'TRV/TTZC' ? (
                                                                    <>
                                                                        <FormInput
                                                                            label="Quantity"
                                                                            type="number"
                                                                            value={payment.quantity}
                                                                            onChange={(e) => updateEprPayment(index, 'quantity', e.target.value)}
                                                                            placeholder="0"
                                                                        />
                                                                        <FormInput
                                                                            label="Rate"
                                                                            type="number"
                                                                            step="0.01"
                                                                            value={payment.rate}
                                                                            onChange={(e) => updateEprPayment(index, 'rate', e.target.value)}
                                                                            placeholder="0.00"
                                                                        />
                                                                        <FormInput
                                                                            label="Total"
                                                                            type="number"
                                                                            value={payment.amount}
                                                                            disabled
                                                                            className="bg-gray-100"
                                                                        />
                                                                    </>
                                                                ) : (
                                                                    <div className="col-span-3">
                                                                        <FormInput
                                                                            label="Amount"
                                                                            type="number"
                                                                            step="0.01"
                                                                            value={payment.amount}
                                                                            onChange={(e) => updateEprPayment(index, 'amount', e.target.value)}
                                                                            placeholder="0.00"
                                                                        />
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                        <div className="col-span-1 flex items-end">
                                                            <Button
                                                                type="button"
                                                                variant="danger"
                                                                size="sm"
                                                                onClick={() => removeEprPayment(index)}
                                                            >
                                                                ×
                                                            </Button>
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <p className="text-sm text-gray-500 italic">No expenses added yet.</p>
                                    )}
                                </div>

                                {/* Net Profit Calculation */}
                                {(data.epr_amount_funded || eprPayments.length > 0) && (
                                    <div className="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                                        <div className="flex justify-between items-center">
                                            <h4 className="text-sm font-semibold text-gray-900">Net Profit:</h4>
                                            <p className="text-lg font-bold text-green-600">
                                                £{(
                                                    (parseFloat(data.epr_amount_funded) || 0) - 
                                                    eprPayments.reduce((sum, p) => sum + parseFloat(p.amount || 0), 0)
                                                ).toFixed(2)}
                                            </p>
                                        </div>
                                        <p className="text-xs text-gray-500 mt-1">
                                            Amount Funded (£{parseFloat(data.epr_amount_funded || 0).toFixed(2)}) - Total Expenses (£{eprPayments.reduce((sum, p) => sum + parseFloat(p.amount || 0), 0).toFixed(2)})
                                        </p>
                                    </div>
                                )}
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

