import { XMarkIcon } from '@heroicons/react/24/outline';
import {
    CheckCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    XCircleIcon,
} from '@heroicons/react/24/solid';

export default function Alert({ type = 'info', message, onClose, className = '' }) {
    const types = {
        success: {
            bg: 'bg-success-50',
            border: 'border-success-400',
            text: 'text-success-800',
            icon: CheckCircleIcon,
            iconColor: 'text-success-400',
        },
        error: {
            bg: 'bg-danger-50',
            border: 'border-danger-400',
            text: 'text-danger-800',
            icon: XCircleIcon,
            iconColor: 'text-danger-400',
        },
        warning: {
            bg: 'bg-warning-50',
            border: 'border-warning-400',
            text: 'text-warning-800',
            icon: ExclamationTriangleIcon,
            iconColor: 'text-warning-400',
        },
        info: {
            bg: 'bg-blue-50',
            border: 'border-blue-400',
            text: 'text-blue-800',
            icon: InformationCircleIcon,
            iconColor: 'text-blue-400',
        },
    };

    const config = types[type];
    const Icon = config.icon;

    return (
        <div className={`rounded-md ${config.bg} ${config.border} border p-4 ${className}`}>
            <div className="flex">
                <div className="flex-shrink-0">
                    <Icon className={`h-5 w-5 ${config.iconColor}`} aria-hidden="true" />
                </div>
                <div className="ml-3 flex-1">
                    <p className={`text-sm font-medium ${config.text}`}>{message}</p>
                </div>
                {onClose && (
                    <div className="ml-3 flex-shrink-0">
                        <button
                            type="button"
                            onClick={onClose}
                            className={`inline-flex rounded-md ${config.bg} ${config.text} hover:bg-opacity-50 focus:outline-none focus:ring-2 focus:ring-offset-2 ${config.border.replace('border-', 'focus:ring-')}`}
                        >
                            <span className="sr-only">Dismiss</span>
                            <XMarkIcon className="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}

