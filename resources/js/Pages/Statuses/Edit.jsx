import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Card, { CardHeader, CardContent } from '@/Components/Card';
import FormInput from '@/Components/FormInput';
import FormTextarea from '@/Components/FormTextarea';
import FormSelect from '@/Components/FormSelect';
import Button from '@/Components/Button';
import Alert from '@/Components/Alert';

export default function EditStatus({ status }) {
    const { data, setData, put, processing, errors } = useForm({
        name: status.name || '',
        description: status.description || '',
        color: status.color || 'secondary',
        sort_order: status.sort_order || '',
        is_active: status.is_active ?? true,
    });

    const colorOptions = [
        { value: 'primary', label: 'Primary' },
        { value: 'secondary', label: 'Secondary' },
        { value: 'success', label: 'Success' },
        { value: 'danger', label: 'Danger' },
        { value: 'warning', label: 'Warning' },
        { value: 'info', label: 'Info' },
    ];

    const submit = (e) => {
        e.preventDefault();
        put(route('statuses.update', status.id));
    };

    return (
        <AppLayout>
            <Head title={`Edit Status - ${status.name}`} />

            <div className="max-w-2xl mx-auto">
                <Card padding={false} className="mb-6">
                    <CardHeader>
                        <div className="flex justify-between items-center">
                            <h2 className="text-xl font-semibold">Edit Status</h2>
                            <div className="flex gap-2">
                                <Link href={route('statuses.show', status.id)}>
                                    <Button variant="secondary" size="sm">View Status</Button>
                                </Link>
                                <Link href={route('statuses.index')}>
                                    <Button variant="secondary" size="sm">Back to Statuses</Button>
                                </Link>
                            </div>
                        </div>
                    </CardHeader>
                </Card>

                {Object.keys(errors).length > 0 && (
                    <Alert type="error" message="Please fix the errors below" className="mb-6" />
                )}

                <form onSubmit={submit}>
                    <Card>
                        <CardHeader>
                            <h3 className="text-lg font-semibold">Status Information</h3>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <FormInput
                                    label="Name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    error={errors.name}
                                    required
                                    placeholder="e.g., New Lead"
                                />

                                <FormTextarea
                                    label="Description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    error={errors.description}
                                    rows={3}
                                    placeholder="Optional description for this status"
                                />

                                <div className="grid grid-cols-2 gap-4">
                                    <FormSelect
                                        label="Color"
                                        value={data.color}
                                        onChange={(e) => setData('color', e.target.value)}
                                        error={errors.color}
                                        required
                                    >
                                        {colorOptions.map((option) => (
                                            <option key={option.value} value={option.value}>
                                                {option.label}
                                            </option>
                                        ))}
                                    </FormSelect>

                                    <FormInput
                                        label="Sort Order"
                                        type="number"
                                        min="0"
                                        value={data.sort_order}
                                        onChange={(e) => setData('sort_order', e.target.value)}
                                        error={errors.sort_order}
                                        placeholder="0"
                                    />
                                </div>

                                <div className="flex items-center">
                                    <input
                                        type="checkbox"
                                        id="is_active"
                                        checked={data.is_active}
                                        onChange={(e) => setData('is_active', e.target.checked)}
                                        className="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                                    />
                                    <label htmlFor="is_active" className="ml-2 text-sm text-gray-700">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex justify-end gap-4 mt-6">
                        <Link href={route('statuses.index')}>
                            <Button type="button" variant="secondary">Cancel</Button>
                        </Link>
                        <Button type="submit" variant="primary" disabled={processing}>
                            {processing ? 'Updating...' : 'Update Status'}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
