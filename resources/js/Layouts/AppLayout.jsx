import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import {
    Bars3Icon,
    XMarkIcon,
    HomeIcon,
    UserGroupIcon,
    UsersIcon,
    DocumentTextIcon,
    ChartBarIcon,
    Cog6ToothIcon,
    ArrowLeftOnRectangleIcon,
    ArrowDownTrayIcon,
} from '@heroicons/react/24/outline';
import Alert from '@/Components/Alert';

export default function AppLayout({ children, header }) {
    const { auth, flash } = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [flashMessage, setFlashMessage] = useState(flash);

    const navigation = [
        { name: 'Dashboard', href: route('dashboard'), icon: HomeIcon },
        { name: 'Leads', href: route('leads.index'), icon: UserGroupIcon },
        { name: 'Import', href: route('import.index'), icon: ArrowDownTrayIcon },
        { name: 'Users', href: route('users.index'), icon: UsersIcon },
        { name: 'Activities', href: route('activities.index'), icon: DocumentTextIcon },
        { name: 'Reports', href: route('reports.index'), icon: ChartBarIcon },
        { name: 'Settings', href: route('settings.index'), icon: Cog6ToothIcon },
    ];

    const handleLogout = () => {
        router.post(route('logout'));
    };

    return (
        <div className="min-h-screen bg-gray-50">
            {/* Mobile sidebar */}
            <div
                className={`fixed inset-0 z-40 lg:hidden ${
                    sidebarOpen ? 'block' : 'hidden'
                }`}
            >
                <div className="fixed inset-0 bg-gray-600 bg-opacity-75" onClick={() => setSidebarOpen(false)} />
                <div className="fixed inset-y-0 left-0 flex flex-col w-64 bg-white">
                    <div className="flex items-center justify-between h-16 px-4 border-b border-gray-200">
                        <img
                            className="h-8 w-auto"
                            src="/images/logo.svg"
                            alt="Radiance Eco"
                        />
                        <button
                            onClick={() => setSidebarOpen(false)}
                            className="text-gray-500 hover:text-gray-600"
                        >
                            <XMarkIcon className="h-6 w-6" />
                        </button>
                    </div>
                    <nav className="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                        {navigation.map((item) => (
                            <Link
                                key={item.name}
                                href={item.href}
                                className="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                            >
                                <item.icon className="mr-3 h-6 w-6" />
                                {item.name}
                            </Link>
                        ))}
                    </nav>
                </div>
            </div>

            {/* Desktop sidebar */}
            <div className="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
                <div className="flex flex-col flex-grow border-r border-gray-200 bg-white overflow-y-auto">
                    <div className="flex items-center flex-shrink-0 px-4 h-16 border-b border-gray-200">
                        <img
                            className="h-8 w-auto"
                            src="/images/logo.svg"
                            alt="Radiance Eco"
                        />
                        <span className="ml-2 text-xl font-bold text-gray-900">Radiance Eco</span>
                    </div>
                    <nav className="flex-1 px-4 py-4 space-y-1">
                        {navigation.map((item) => (
                            <Link
                                key={item.name}
                                href={item.href}
                                className="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                            >
                                <item.icon className="mr-3 h-6 w-6" />
                                {item.name}
                            </Link>
                        ))}
                    </nav>
                    <div className="flex-shrink-0 flex border-t border-gray-200 p-4">
                        <div className="flex items-center w-full">
                            <div className="flex-1">
                                <p className="text-sm font-medium text-gray-700">{auth.user?.name}</p>
                                <p className="text-xs text-gray-500">{auth.user?.email}</p>
                            </div>
                            <button
                                onClick={handleLogout}
                                className="ml-3 p-2 text-gray-400 hover:text-gray-600"
                            >
                                <ArrowLeftOnRectangleIcon className="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Main content */}
            <div className="lg:pl-64 flex flex-col flex-1">
                {/* Top bar */}
                <div className="sticky top-0 z-10 flex-shrink-0 flex h-16 bg-white border-b border-gray-200 lg:hidden">
                    <button
                        onClick={() => setSidebarOpen(true)}
                        className="px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500"
                    >
                        <Bars3Icon className="h-6 w-6" />
                    </button>
                    <div className="flex-1 flex items-center justify-between px-4">
                        <img className="h-8 w-auto" src="/images/logo.svg" alt="Starline Care" />
                    </div>
                </div>

                <main className="flex-1">
                    {/* Flash messages */}
                    {flashMessage?.success && (
                        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                            <Alert
                                type="success"
                                message={flashMessage.success}
                                onClose={() => setFlashMessage({ ...flashMessage, success: null })}
                            />
                        </div>
                    )}
                    {flashMessage?.error && (
                        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                            <Alert
                                type="error"
                                message={flashMessage.error}
                                onClose={() => setFlashMessage({ ...flashMessage, error: null })}
                            />
                        </div>
                    )}
                    {flashMessage?.warning && (
                        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                            <Alert
                                type="warning"
                                message={flashMessage.warning}
                                onClose={() => setFlashMessage({ ...flashMessage, warning: null })}
                            />
                        </div>
                    )}
                    {flashMessage?.info && (
                        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                            <Alert
                                type="info"
                                message={flashMessage.info}
                                onClose={() => setFlashMessage({ ...flashMessage, info: null })}
                            />
                        </div>
                    )}

                    {header && (
                        <header className="bg-white border-b border-gray-200">
                            <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                {header}
                            </div>
                        </header>
                    )}

                    <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}

