import { forwardRef } from 'react';

export default forwardRef(function FormSelect(
    { label, error, className = '', required = false, children, ...props },
    ref
) {
    return (
        <div className={className}>
            {label && (
                <label className="block text-sm font-medium text-gray-700 mb-1">
                    {label}
                    {required && <span className="text-danger-500 ml-1">*</span>}
                </label>
            )}
            <select
                {...props}
                className={`
                    block w-full rounded-md shadow-sm
                    focus:ring-primary-500 focus:border-primary-500
                    ${error
                        ? 'border-danger-300 text-danger-900 focus:ring-danger-500 focus:border-danger-500'
                        : 'border-gray-300'
                    }
                    sm:text-sm
                `}
                ref={ref}
            >
                {children}
            </select>
            {error && (
                <p className="mt-1 text-sm text-danger-600">{error}</p>
            )}
        </div>
    );
});

