export default function FormSection({ title, description, children, className = '' }) {
    return (
        <div className={`bg-white shadow-sm rounded-lg overflow-hidden ${className}`}>
            {(title || description) && (
                <div className="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    {title && (
                        <h3 className="text-lg font-medium leading-6 text-gray-900">
                            {title}
                        </h3>
                    )}
                    {description && (
                        <p className="mt-1 text-sm text-gray-500">
                            {description}
                        </p>
                    )}
                </div>
            )}
            <div className="px-6 py-6">
                {children}
            </div>
        </div>
    );
}

