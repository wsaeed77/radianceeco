export default function Button({
    type = 'button',
    variant = 'primary',
    size = 'md',
    className = '',
    disabled = false,
    children,
    ...props
}) {
    const variants = {
        primary: 'bg-primary-600 hover:bg-primary-700 text-white focus:ring-primary-500',
        secondary: 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 focus:ring-primary-500',
        success: 'bg-success-600 hover:bg-success-700 text-white focus:ring-success-500',
        danger: 'bg-danger-600 hover:bg-danger-700 text-white focus:ring-danger-500',
        warning: 'bg-warning-600 hover:bg-warning-700 text-white focus:ring-warning-500',
    };

    const sizes = {
        sm: 'px-3 py-1.5 text-sm',
        md: 'px-4 py-2 text-sm',
        lg: 'px-6 py-3 text-base',
    };

    return (
        <button
            {...props}
            type={type}
            disabled={disabled}
            className={`
                inline-flex items-center justify-center font-medium rounded-md
                shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2
                disabled:opacity-50 disabled:cursor-not-allowed
                transition-colors duration-200
                ${variants[variant]}
                ${sizes[size]}
                ${className}
            `}
        >
            {children}
        </button>
    );
}

