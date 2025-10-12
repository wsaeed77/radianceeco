import { PlusIcon } from '@heroicons/react/24/outline';

export default function EmptyState({
    icon: Icon,
    title,
    description,
    action,
    actionLabel = 'Create New',
    className = '',
}) {
    return (
        <div className={`text-center py-12 ${className}`}>
            <div className="mx-auto h-12 w-12 text-gray-400">
                {Icon ? <Icon className="h-full w-full" /> : null}
            </div>
            <h3 className="mt-2 text-sm font-semibold text-gray-900">{title}</h3>
            {description && (
                <p className="mt-1 text-sm text-gray-500">{description}</p>
            )}
            {action && (
                <div className="mt-6">
                    <button
                        type="button"
                        onClick={action}
                        className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        <PlusIcon className="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
                        {actionLabel}
                    </button>
                </div>
            )}
        </div>
    );
}

