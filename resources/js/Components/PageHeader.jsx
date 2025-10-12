export default function PageHeader({ title, description, actions, breadcrumbs }) {
    return (
        <div className="mb-8">
            {breadcrumbs && (
                <nav className="flex mb-4" aria-label="Breadcrumb">
                    <ol className="flex items-center space-x-2">
                        {breadcrumbs.map((crumb, index) => (
                            <li key={index} className="flex items-center">
                                {index > 0 && (
                                    <svg
                                        className="flex-shrink-0 h-5 w-5 text-gray-400 mx-2"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                                    </svg>
                                )}
                                {crumb.href ? (
                                    <a
                                        href={crumb.href}
                                        className="text-sm font-medium text-gray-500 hover:text-gray-700"
                                    >
                                        {crumb.label}
                                    </a>
                                ) : (
                                    <span className="text-sm font-medium text-gray-900">
                                        {crumb.label}
                                    </span>
                                )}
                            </li>
                        ))}
                    </ol>
                </nav>
            )}
            <div className="md:flex md:items-center md:justify-between">
                <div className="flex-1 min-w-0">
                    <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        {title}
                    </h2>
                    {description && (
                        <p className="mt-1 text-sm text-gray-500">{description}</p>
                    )}
                </div>
                {actions && (
                    <div className="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                        {actions}
                    </div>
                )}
            </div>
        </div>
    );
}

