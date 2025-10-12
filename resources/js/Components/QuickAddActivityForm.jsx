import { useForm } from '@inertiajs/react';
import FormSelect from './FormSelect';
import Button from './Button';

export default function QuickAddActivityForm({ leadId, activityTypes }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        lead_id: leadId,
        type: '',
        description: '',
        message: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('activities.store'), {
            onSuccess: () => {
                reset('type', 'description', 'message');
            },
        });
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-3">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div className="md:col-span-1">
                    <select
                        value={data.type}
                        onChange={(e) => setData('type', e.target.value)}
                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                        required
                    >
                        <option value="">Select Type</option>
                        {activityTypes?.map((type) => (
                            <option key={type.value} value={type.value}>
                                {type.name}
                            </option>
                        ))}
                    </select>
                    {errors.type && (
                        <p className="mt-1 text-xs text-danger-600">{errors.type}</p>
                    )}
                </div>
                <div className="md:col-span-2">
                    <input
                        type="text"
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        placeholder="Description"
                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                        required
                    />
                    {errors.description && (
                        <p className="mt-1 text-xs text-danger-600">{errors.description}</p>
                    )}
                </div>
            </div>

            <div>
                <textarea
                    value={data.message}
                    onChange={(e) => setData('message', e.target.value)}
                    placeholder="Details (optional)"
                    rows="3"
                    className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                />
                {errors.message && (
                    <p className="mt-1 text-xs text-danger-600">{errors.message}</p>
                )}
            </div>

            <div className="flex justify-end">
                <Button type="submit" variant="success" size="sm" disabled={processing}>
                    {processing ? 'Adding...' : 'Add Activity'}
                </Button>
            </div>
        </form>
    );
}

