import React, { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { MapContainer, TileLayer, Marker, Popup, useMap } from 'react-leaflet';
import MarkerClusterGroup from 'react-leaflet-cluster';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import axios from 'axios';
import { EyeIcon, FunnelIcon, XMarkIcon } from '@heroicons/react/24/outline';

// Fix for default marker icons in Leaflet
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

// Custom marker icons by status
const getMarkerIcon = (status) => {
    const colors = {
        'new': '#10b981', // green
        'hold': '#f59e0b', // yellow
        'not_possible': '#ef4444', // red
        'property_installed': '#6b7280', // gray
        'unknown': '#9ca3af', // light gray
    };

    const color = colors[status] || '#3b82f6'; // default blue

    return L.divIcon({
        className: 'custom-marker',
        html: `<div style="background-color: ${color}; width: 25px; height: 25px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
        iconSize: [25, 25],
        iconAnchor: [12, 12],
    });
};

// Component to fit map bounds to markers
function FitBounds({ leads }) {
    const map = useMap();

    useEffect(() => {
        if (leads && leads.length > 0) {
            const bounds = L.latLngBounds(leads.map(lead => [lead.latitude, lead.longitude]));
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }, [leads, map]);

    return null;
}

export default function MapIndex({ statuses, stages, sources }) {
    const [leads, setLeads] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showFilters, setShowFilters] = useState(true);
    const [filters, setFilters] = useState({
        status: '',
        stage: '',
        source: '',
        search: '',
    });

    // Fetch leads data
    const fetchLeads = async () => {
        setLoading(true);
        try {
            const response = await axios.get(route('map.leads'), { params: filters });
            setLeads(response.data.leads);
        } catch (error) {
            console.error('Error fetching leads:', error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchLeads();
    }, [filters]);

    const handleFilterChange = (key, value) => {
        setFilters(prev => ({ ...prev, [key]: value }));
    };

    const clearFilters = () => {
        setFilters({
            status: '',
            stage: '',
            source: '',
            search: '',
        });
    };

    // UK center coordinates
    const ukCenter = [54.5, -3.5];

    return (
        <AppLayout>
            <Head title="Leads Map" />

            <div className="py-6">
                <div className="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">Leads Map</h1>
                            <p className="mt-1 text-sm text-gray-600">
                                {loading ? 'Loading...' : `Showing ${leads.length} leads on map`}
                            </p>
                        </div>
                        <button
                            onClick={() => setShowFilters(!showFilters)}
                            className="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                        >
                            <FunnelIcon className="h-5 w-5 mr-2" />
                            {showFilters ? 'Hide' : 'Show'} Filters
                        </button>
                    </div>

                    <div className="flex gap-6">
                        {/* Filters Sidebar */}
                        {showFilters && (
                            <div className="w-64 flex-shrink-0">
                                <div className="bg-white rounded-lg shadow p-4 sticky top-6">
                                    <div className="flex items-center justify-between mb-4">
                                        <h3 className="text-lg font-semibold text-gray-900">Filters</h3>
                                        <button
                                            onClick={clearFilters}
                                            className="text-sm text-indigo-600 hover:text-indigo-800"
                                        >
                                            Clear All
                                        </button>
                                    </div>

                                    <div className="space-y-4">
                                        {/* Search */}
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Search
                                            </label>
                                            <input
                                                type="text"
                                                value={filters.search}
                                                onChange={(e) => handleFilterChange('search', e.target.value)}
                                                placeholder="Name, postcode..."
                                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>

                                        {/* Status Filter */}
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Status
                                            </label>
                                            <select
                                                value={filters.status}
                                                onChange={(e) => handleFilterChange('status', e.target.value)}
                                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                                <option value="">All Statuses</option>
                                                {statuses.map(status => (
                                                    <option key={status.value} value={status.value}>
                                                        {status.label}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>

                                        {/* Team Filter */}
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Team
                                            </label>
                                            <select
                                                value={filters.stage}
                                                onChange={(e) => handleFilterChange('stage', e.target.value)}
                                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                                <option value="">All Teams</option>
                                                {stages.map(stage => (
                                                    <option key={stage.value} value={stage.value}>
                                                        {stage.label}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>

                                        {/* Source Filter */}
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Source
                                            </label>
                                            <select
                                                value={filters.source}
                                                onChange={(e) => handleFilterChange('source', e.target.value)}
                                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                                <option value="">All Sources</option>
                                                {sources.map(source => (
                                                    <option key={source.value} value={source.value}>
                                                        {source.label}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                    </div>

                                    {/* Legend */}
                                    <div className="mt-6 pt-6 border-t border-gray-200">
                                        <h4 className="text-sm font-semibold text-gray-900 mb-3">Legend</h4>
                                        <div className="space-y-2 text-xs">
                                            <div className="flex items-center">
                                                <div className="w-4 h-4 rounded-full bg-green-500 mr-2"></div>
                                                <span>New</span>
                                            </div>
                                            <div className="flex items-center">
                                                <div className="w-4 h-4 rounded-full bg-blue-500 mr-2"></div>
                                                <span>In Progress</span>
                                            </div>
                                            <div className="flex items-center">
                                                <div className="w-4 h-4 rounded-full bg-yellow-500 mr-2"></div>
                                                <span>Hold</span>
                                            </div>
                                            <div className="flex items-center">
                                                <div className="w-4 h-4 rounded-full bg-red-500 mr-2"></div>
                                                <span>Not Possible</span>
                                            </div>
                                            <div className="flex items-center">
                                                <div className="w-4 h-4 rounded-full bg-gray-500 mr-2"></div>
                                                <span>Completed</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Map Container */}
                        <div className="flex-1">
                            <div className="bg-white rounded-lg shadow overflow-hidden" style={{ height: '75vh' }}>
                                {loading ? (
                                    <div className="flex items-center justify-center h-full">
                                        <div className="text-center">
                                            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
                                            <p className="mt-4 text-gray-600">Loading map...</p>
                                        </div>
                                    </div>
                                ) : leads.length === 0 ? (
                                    <div className="flex items-center justify-center h-full">
                                        <div className="text-center">
                                            <p className="text-gray-600">No leads found with coordinates.</p>
                                            <p className="text-sm text-gray-500 mt-2">
                                                Leads need to be geocoded first.
                                            </p>
                                        </div>
                                    </div>
                                ) : (
                                    <MapContainer
                                        center={ukCenter}
                                        zoom={6}
                                        style={{ height: '100%', width: '100%' }}
                                        scrollWheelZoom={true}
                                    >
                                        <TileLayer
                                            attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                            url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                                        />

                                        <FitBounds leads={leads} />

                                        <MarkerClusterGroup>
                                            {leads.map((lead) => (
                                                <Marker
                                                    key={lead.id}
                                                    position={[lead.latitude, lead.longitude]}
                                                    icon={getMarkerIcon(lead.status)}
                                                >
                                                    <Popup>
                                                        <div className="p-2 min-w-[200px]">
                                                            <h3 className="font-semibold text-gray-900 mb-2">
                                                                {lead.name}
                                                            </h3>
                                                            <div className="space-y-1 text-sm text-gray-600">
                                                                <p>{lead.address}</p>
                                                                {lead.phone && <p>📞 {lead.phone}</p>}
                                                                {lead.email && <p>✉️ {lead.email}</p>}
                                                                <p className="pt-2 border-t border-gray-200">
                                                                    <span className="font-medium">Status:</span> {lead.status_label}
                                                                </p>
                                                                <p>
                                                                    <span className="font-medium">Team:</span> {lead.stage_label}
                                                                </p>
                                                            </div>
                                                            <div className="mt-3 pt-3 border-t border-gray-200">
                                                                <Link
                                                                    href={route('leads.show', lead.id)}
                                                                    className="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800"
                                                                >
                                                                    <EyeIcon className="h-4 w-4 mr-1" />
                                                                    View Details
                                                                </Link>
                                                            </div>
                                                        </div>
                                                    </Popup>
                                                </Marker>
                                            ))}
                                        </MarkerClusterGroup>
                                    </MapContainer>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

