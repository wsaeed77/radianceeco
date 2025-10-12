import { forwardRef, useEffect, useRef } from 'react';

export default forwardRef(function FormInput(
    { type = 'text', label, error, className = '', isFocused = false, required = false, ...props },
    ref
) {
    const input = ref ? ref : useRef();

    useEffect(() => {
        if (isFocused) {
            input.current.focus();
        }
    }, []);

    return (
        <div className={className}>
            {label && (
                <label className="block text-sm font-medium text-gray-700 mb-1">
                    {label}
                    {required && <span className="text-danger-500 ml-1">*</span>}
                </label>
            )}
            <input
                {...props}
                type={type}
                className={`
                    block w-full rounded-md shadow-sm
                    focus:ring-primary-500 focus:border-primary-500
                    ${error
                        ? 'border-danger-300 text-danger-900 placeholder-danger-300 focus:ring-danger-500 focus:border-danger-500'
                        : 'border-gray-300'
                    }
                    sm:text-sm
                `}
                ref={input}
            />
            {error && (
                <p className="mt-1 text-sm text-danger-600">{error}</p>
            )}
        </div>
    );
});

