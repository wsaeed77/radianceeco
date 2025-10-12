import Card from './Card';

export default function StatCard({ title, value, icon: Icon, trend, trendLabel, color = 'primary' }) {
    const colors = {
        primary: 'bg-primary-50 text-primary-600',
        success: 'bg-success-50 text-success-600',
        warning: 'bg-warning-50 text-warning-600',
        danger: 'bg-danger-50 text-danger-600',
        info: 'bg-blue-50 text-blue-600',
    };

    return (
        <Card>
            <div className="flex items-center">
                <div className="flex-shrink-0">
                    {Icon && (
                        <div className={`p-3 rounded-lg ${colors[color]}`}>
                            <Icon className="h-6 w-6" />
                        </div>
                    )}
                </div>
                <div className="ml-5 w-0 flex-1">
                    <dl>
                        <dt className="text-sm font-medium text-gray-500 truncate">
                            {title}
                        </dt>
                        <dd className="flex items-baseline">
                            <div className="text-2xl font-semibold text-gray-900">
                                {value}
                            </div>
                            {trend && (
                                <div className={`ml-2 flex items-baseline text-sm font-semibold ${
                                    trend > 0 ? 'text-success-600' : 'text-danger-600'
                                }`}>
                                    {trend > 0 ? '+' : ''}{trend}%
                                </div>
                            )}
                        </dd>
                        {trendLabel && (
                            <dd className="mt-1 text-xs text-gray-500">
                                {trendLabel}
                            </dd>
                        )}
                    </dl>
                </div>
            </div>
        </Card>
    );
}

