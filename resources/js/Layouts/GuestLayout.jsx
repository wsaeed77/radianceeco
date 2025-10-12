import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50">
            <div className="mb-8">
                <Link href="/">
                    <img
                        className="h-20 w-auto"
                        src="/images/logo.svg"
                        alt="Radiance Eco"
                    />
                </Link>
            </div>

            <div className="w-full sm:max-w-md px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {children}
            </div>

            <div className="mt-6 text-center text-sm text-gray-600">
                <p>&copy; {new Date().getFullYear()} Radiance Eco. All rights reserved.</p>
            </div>
        </div>
    );
}

