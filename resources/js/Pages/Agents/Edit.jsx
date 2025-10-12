import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Card, { CardHeader, CardContent } from '@/Components/Card';
import FormInput from '@/Components/FormInput';
import Button from '@/Components/Button';
import Alert from '@/Components/Alert';

export default function EditAgent({ agent }) {
    const { data, setData, put, processing, errors } = useForm({
        name: agent.name || '',
        email: agent.email || '',
        phone: agent.phone || '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('agents.update', agent.id));
    };

    return (
        <AppLayout>
            <Head title={`Edit Agent - ${agent.name}`} />

            <div className="max-w-3xl mx-auto">
                <Card padding={false} className="mb-6">
                    <CardHeader>
                        <div className="flex justify-between items-center">
                            <h2 className="text-xl font-semibold">Edit Agent</h2>
                            <Link href={route('agents.index')}>
                                <Button variant="secondary" size="sm">Back to Agents</Button>
                            </Link>
                        </div>
                    </CardHeader>
                </Card>

                {Object.keys(errors).length > 0 && (
                    <Alert type="error" message="Please fix the errors below" className="mb-6" />
                )}

                <form onSubmit={submit}>
                    <Card padding={false}>
                        <CardHeader className="bg-primary-600">
                            <h3 className="text-lg font-semibold text-white">Agent Information</h3>
                        </CardHeader>
                        <CardContent className="p-6">
                            <div className="space-y-4">
                                <FormInput
                                    label="Full Name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    error={errors.name}
                                    required
                                />

                                <FormInput
                                    label="Email Address"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    error={errors.email}
                                    required
                                />

                                <FormInput
                                    label="Phone Number"
                                    value={data.phone}
                                    onChange={(e) => setData('phone', e.target.value)}
                                    error={errors.phone}
                                />

                                <div className="border-t pt-4 mt-6">
                                    <p className="text-sm text-gray-600 mb-4">
                                        Leave password fields blank to keep the current password
                                    </p>

                                    <FormInput
                                        label="New Password"
                                        type="password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        error={errors.password}
                                    />

                                    <FormInput
                                        label="Confirm New Password"
                                        type="password"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        error={errors.password_confirmation}
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="mt-6">
                        <div className="flex justify-center gap-4">
                            <Button type="submit" variant="primary" disabled={processing}>
                                {processing ? 'Updating...' : 'Update Agent'}
                            </Button>
                            <Link href={route('agents.index')}>
                                <Button type="button" variant="secondary">Cancel</Button>
                            </Link>
                        </div>
                    </Card>
                </form>
            </div>
        </AppLayout>
    );
}

