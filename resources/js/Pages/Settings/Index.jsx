import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';
import Card, { CardHeader, CardTitle, CardContent } from '@/Components/Card';
import Button from '@/Components/Button';
import { CheckCircleIcon } from '@heroicons/react/24/outline';

export default function SettingsIndex({ eco4Settings, flash }) {
    const [values, setValues] = useState(
        eco4Settings.reduce((acc, setting) => {
            acc[setting.id] = setting.value;
            return acc;
        }, {})
    );
    const [saving, setSaving] = useState({});

    const handleUpdate = (setting) => {
        setSaving({ ...saving, [setting.id]: true });
        
        router.put(route('settings.update', setting.id), {
            value: values[setting.id]
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setSaving({ ...saving, [setting.id]: false });
            },
            onError: () => {
                setSaving({ ...saving, [setting.id]: false });
            }
        });
    };

    const handleChange = (settingId, value) => {
        setValues({ ...values, [settingId]: value });
    };

    const getInputType = (type) => {
        switch(type) {
            case 'integer':
            case 'float':
                return 'number';
            case 'boolean':
                return 'checkbox';
            default:
                return 'text';
        }
    };

    const getInputStep = (type) => {
        if (type === 'float') return '0.01';
        if (type === 'integer') return '1';
        return undefined;
    };

    return (
        <AppLayout>
            <Head title="Settings" />

            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <PageHeader
                        title="Settings"
                        subtitle="Configure system settings and calculator parameters"
                    />

                    {flash?.success && (
                        <div className="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center text-green-800">
                            <CheckCircleIcon className="h-5 w-5 mr-2" />
                            {flash.success}
                        </div>
                    )}

                    {/* ECO4 Calculator Settings */}
                    <Card className="mb-6">
                        <CardHeader>
                            <CardTitle>ECO4 Calculator Settings</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-6">
                                {eco4Settings.map((setting) => (
                                    <div key={setting.id} className="border-b last:border-b-0 pb-6 last:pb-0">
                                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                            {/* Label & Description */}
                                            <div className="lg:col-span-1">
                                                <label className="block text-sm font-semibold text-gray-900 mb-1">
                                                    {setting.label}
                                                </label>
                                                {setting.description && (
                                                    <p className="text-sm text-gray-500">
                                                        {setting.description}
                                                    </p>
                                                )}
                                                <p className="text-xs text-gray-400 mt-1">
                                                    Key: <code className="bg-gray-100 px-1 rounded">{setting.key}</code>
                                                </p>
                                            </div>

                                            {/* Input */}
                                            <div className="lg:col-span-1 flex items-center">
                                                {setting.type === 'boolean' ? (
                                                    <input
                                                        type="checkbox"
                                                        checked={values[setting.id] === 'true' || values[setting.id] === true}
                                                        onChange={(e) => handleChange(setting.id, e.target.checked ? 'true' : 'false')}
                                                        className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                    />
                                                ) : (
                                                    <input
                                                        type={getInputType(setting.type)}
                                                        step={getInputStep(setting.type)}
                                                        value={values[setting.id] || ''}
                                                        onChange={(e) => handleChange(setting.id, e.target.value)}
                                                        className="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                    />
                                                )}
                                            </div>

                                            {/* Action Button */}
                                            <div className="lg:col-span-1 flex items-center lg:justify-end">
                                                <Button
                                                    onClick={() => handleUpdate(setting)}
                                                    disabled={saving[setting.id] || values[setting.id] === setting.value}
                                                    variant="primary"
                                                    size="sm"
                                                >
                                                    {saving[setting.id] ? 'Saving...' : 'Update'}
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Info Card */}
                    <Card>
                        <CardContent>
                            <h3 className="text-sm font-semibold text-gray-900 mb-2">📝 Note</h3>
                            <ul className="text-sm text-gray-600 space-y-1 list-disc list-inside">
                                <li>Changes take effect immediately for new calculations</li>
                                <li>Saved calculations retain the rate at the time they were created</li>
                                <li>Settings are cached for performance (cleared automatically on update)</li>
                                <li>You can also update via CLI: <code className="bg-gray-100 px-2 py-0.5 rounded text-xs">php artisan setting:update [key] [value]</code></li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}

