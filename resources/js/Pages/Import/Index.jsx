import { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';
import Card, { CardHeader, CardTitle, CardContent } from '@/Components/Card';
import Button from '@/Components/Button';
import { CheckCircleIcon, DocumentTextIcon, ArrowRightIcon, ArrowLeftIcon } from '@heroicons/react/24/outline';
import axios from 'axios';

export default function ImportIndex() {
    const [currentStep, setCurrentStep] = useState(1);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    
    // Step 1: List sheets
    const [sheets, setSheets] = useState([]);
    const [selectedSheet, setSelectedSheet] = useState(null);
    const [manualSpreadsheetId, setManualSpreadsheetId] = useState('');
    const [useManualId, setUseManualId] = useState(false);
    
    // Step 2: Select tab
    const [sheetInfo, setSheetInfo] = useState(null);
    const [selectedTab, setSelectedTab] = useState(null);
    
    // Step 3: Preview & Map
    const [preview, setPreview] = useState(null);
    const [leadFields, setLeadFields] = useState([]);
    const [columnMapping, setColumnMapping] = useState({});
    
    // Step 4: Import options
    const [skipDuplicates, setSkipDuplicates] = useState(true);
    const [updateExisting, setUpdateExisting] = useState(false);
    
    // Step 5: Results
    const [importResults, setImportResults] = useState(null);

    const steps = [
        { number: 1, name: 'Select Sheet', description: 'Choose a Google Sheet' },
        { number: 2, name: 'Select Tab', description: 'Pick a sheet tab' },
        { number: 3, name: 'Map Columns', description: 'Map columns to fields' },
        { number: 4, name: 'Import', description: 'Import leads' },
    ];

    // Load Google Sheets on mount
    useEffect(() => {
        if (!useManualId) {
            loadSheets();
        }
    }, [useManualId]);

    const loadSheets = async () => {
        setLoading(true);
        setError(null);
        try {
            const response = await axios.post('/import/sheets/list');
            setSheets(response.data.sheets);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to load sheets');
        } finally {
            setLoading(false);
        }
    };

    const handleSelectSheet = async (sheet) => {
        setSelectedSheet(sheet);
        setLoading(true);
        setError(null);
        try {
            const response = await axios.post('/import/sheets/info', {
                spreadsheet_id: sheet.id
            });
            setSheetInfo(response.data.info);
            setCurrentStep(2);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to load sheet info');
        } finally {
            setLoading(false);
        }
    };

    const handleSelectTab = async (tab) => {
        setSelectedTab(tab);
        setLoading(true);
        setError(null);
        try {
            const response = await axios.post('/import/sheets/preview', {
                spreadsheet_id: selectedSheet.id,
                sheet_name: tab.title,
                limit: 10
            });
            setPreview(response.data.preview);
            setLeadFields(response.data.lead_fields);
            
            // Auto-map columns based on similarity
            const autoMapping = {};
            response.data.preview.headers.forEach(header => {
                const normalized = header.toLowerCase().replace(/\s+/g, '_');
                const matchingField = response.data.lead_fields.find(f => 
                    f.value === normalized || 
                    f.label.toLowerCase() === header.toLowerCase()
                );
                if (matchingField) {
                    autoMapping[header] = matchingField.value;
                }
            });
            setColumnMapping(autoMapping);
            
            setCurrentStep(3);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to preview sheet');
        } finally {
            setLoading(false);
        }
    };

    const handleImport = async () => {
        setLoading(true);
        setError(null);
        try {
            const response = await axios.post('/import/leads', {
                spreadsheet_id: selectedSheet.id,
                sheet_name: selectedTab.title,
                mapping: columnMapping,
                skip_duplicates: skipDuplicates,
                update_existing: updateExisting,
            });
            setImportResults(response.data);
            setCurrentStep(4);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to import leads');
        } finally {
            setLoading(false);
        }
    };

    const resetImport = () => {
        setCurrentStep(1);
        setSelectedSheet(null);
        setSheetInfo(null);
        setSelectedTab(null);
        setPreview(null);
        setColumnMapping({});
        setImportResults(null);
        setError(null);
        loadSheets();
    };

    return (
        <AppLayout>
            <Head title="Import Leads" />

            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <PageHeader
                        title="Import Leads"
                        subtitle="Import leads from Google Sheets"
                    />

                    {/* Progress Steps */}
                    <div className="mb-8">
                        <nav aria-label="Progress">
                            <ol className="flex items-center justify-center space-x-5">
                                {steps.map((step, index) => (
                                    <li key={step.number} className="relative">
                                        {index !== 0 && (
                                            <div className="absolute top-4 -left-3 w-full h-0.5 bg-gray-200">
                                                <div 
                                                    className={`h-full transition-all ${
                                                        currentStep > step.number ? 'bg-blue-600' : 'bg-gray-200'
                                                    }`}
                                                    style={{ width: currentStep > step.number ? '100%' : '0%' }}
                                                />
                                            </div>
                                        )}
                                        <div className="relative flex flex-col items-center">
                                            <span
                                                className={`w-8 h-8 flex items-center justify-center border-2 rounded-full ${
                                                    currentStep >= step.number
                                                        ? 'border-blue-600 bg-blue-600 text-white'
                                                        : 'border-gray-300 bg-white text-gray-500'
                                                }`}
                                            >
                                                {currentStep > step.number ? (
                                                    <CheckCircleIcon className="w-5 h-5" />
                                                ) : (
                                                    step.number
                                                )}
                                            </span>
                                            <span className="mt-2 text-xs font-medium text-gray-600">
                                                {step.name}
                                            </span>
                                        </div>
                                    </li>
                                ))}
                            </ol>
                        </nav>
                    </div>

                    {/* Error Display */}
                    {error && (
                        <div className="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                            {error}
                        </div>
                    )}

                    {/* Step Content */}
                    <Card>
                        <CardContent className="p-6">
                            {/* Step 1: Select Sheet */}
                            {currentStep === 1 && (
                                <div>
                                    <h3 className="text-lg font-semibold mb-4">Select a Google Sheet</h3>
                                    
                                    {/* Manual Spreadsheet ID Option */}
                                    <div className="mb-6 p-4 border border-blue-200 bg-blue-50 rounded-lg">
                                        <label className="flex items-center mb-3">
                                            <input
                                                type="checkbox"
                                                checked={useManualId}
                                                onChange={(e) => setUseManualId(e.target.checked)}
                                                className="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                            />
                                            <span className="ml-2 text-sm font-medium text-gray-700">
                                                Use Spreadsheet ID directly
                                            </span>
                                        </label>
                                        
                                        {useManualId && (
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                                    Enter Google Sheets ID or URL
                                                </label>
                                                <input
                                                    type="text"
                                                    value={manualSpreadsheetId}
                                                    onChange={(e) => setManualSpreadsheetId(e.target.value)}
                                                    placeholder="1AbC...XyZ or https://docs.google.com/spreadsheets/d/1AbC...XyZ/edit"
                                                    className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                                />
                                                <p className="mt-2 text-xs text-gray-500">
                                                    Paste the full URL or just the spreadsheet ID from the URL
                                                </p>
                                                <Button
                                                    className="mt-3"
                                                    onClick={() => {
                                                        let id = manualSpreadsheetId.trim();
                                                        // Extract ID from URL if pasted
                                                        const match = id.match(/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/);
                                                        if (match) {
                                                            id = match[1];
                                                        }
                                                        if (id) {
                                                            handleSelectSheet({ id: id, name: 'Manual Sheet' });
                                                        }
                                                    }}
                                                    disabled={!manualSpreadsheetId.trim() || loading}
                                                >
                                                    Continue with this Sheet
                                                </Button>
                                            </div>
                                        )}
                                    </div>
                                    
                                    {!useManualId && (
                                        <>
                                            {loading ? (
                                                <div className="text-center py-8">
                                                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                                                    <p className="mt-4 text-gray-600">Loading sheets...</p>
                                                </div>
                                            ) : (
                                                <div className="space-y-2">
                                                    {sheets.map((sheet) => (
                                                        <button
                                                            key={sheet.id}
                                                            onClick={() => handleSelectSheet(sheet)}
                                                            className="w-full text-left p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors"
                                                        >
                                                            <div className="flex items-center justify-between">
                                                                <div className="flex items-center space-x-3">
                                                                    <DocumentTextIcon className="h-6 w-6 text-green-600" />
                                                                    <div>
                                                                        <p className="font-medium">{sheet.name}</p>
                                                                        <p className="text-sm text-gray-500">
                                                                            Owner: {sheet.owner} • Modified: {new Date(sheet.modified_time).toLocaleDateString()}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <ArrowRightIcon className="h-5 w-5 text-gray-400" />
                                                            </div>
                                                        </button>
                                                    ))}
                                                    {sheets.length === 0 && (
                                                        <p className="text-center py-8 text-gray-500">
                                                            No Google Sheets found. Try using the Spreadsheet ID option above.
                                                        </p>
                                                    )}
                                                </div>
                                            )}
                                        </>
                                    )}
                                </div>
                            )}

                            {/* Step 2: Select Tab */}
                            {currentStep === 2 && sheetInfo && (
                                <div>
                                    <div className="flex items-center justify-between mb-4">
                                        <h3 className="text-lg font-semibold">Select a Sheet Tab from "{sheetInfo.title}"</h3>
                                        <Button variant="outline" size="sm" onClick={() => setCurrentStep(1)}>
                                            <ArrowLeftIcon className="h-4 w-4 mr-1" /> Back
                                        </Button>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {sheetInfo.sheets.map((tab) => (
                                            <button
                                                key={tab.id}
                                                onClick={() => handleSelectTab(tab)}
                                                disabled={loading}
                                                className="text-left p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors disabled:opacity-50"
                                            >
                                                <div className="flex items-center justify-between">
                                                    <div>
                                                        <p className="font-medium">{tab.title}</p>
                                                        <p className="text-sm text-gray-500">
                                                            {tab.row_count} rows × {tab.column_count} columns
                                                        </p>
                                                    </div>
                                                    <ArrowRightIcon className="h-5 w-5 text-gray-400" />
                                                </div>
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Step 3: Map Columns */}
                            {currentStep === 3 && preview && (
                                <div>
                                    <div className="flex items-center justify-between mb-4">
                                        <h3 className="text-lg font-semibold">Map Columns to Lead Fields</h3>
                                        <Button variant="outline" size="sm" onClick={() => setCurrentStep(2)}>
                                            <ArrowLeftIcon className="h-4 w-4 mr-1" /> Back
                                        </Button>
                                    </div>
                                    
                                    {/* Preview Table */}
                                    <div className="mb-6">
                                        <p className="text-sm text-gray-600 mb-2">
                                            Preview ({preview.preview_rows.length} of {preview.total_rows} rows)
                                        </p>
                                        <div className="overflow-x-auto">
                                            <table className="min-w-full divide-y divide-gray-200 border">
                                                <thead className="bg-gray-50">
                                                    <tr>
                                                        {preview.headers.map((header, index) => (
                                                            <th key={index} className="px-3 py-2 text-left text-xs font-medium text-gray-700">
                                                                {header}
                                                            </th>
                                                        ))}
                                                    </tr>
                                                </thead>
                                                <tbody className="bg-white divide-y divide-gray-200">
                                                    {preview.preview_rows.slice(0, 3).map((row, rowIndex) => (
                                                        <tr key={rowIndex}>
                                                            {row.map((cell, cellIndex) => (
                                                                <td key={cellIndex} className="px-3 py-2 text-sm text-gray-600">
                                                                    {cell || '-'}
                                                                </td>
                                                            ))}
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {/* Column Mapping */}
                                    <div className="space-y-4">
                                        <h4 className="font-medium">Column Mapping</h4>
                                        {preview.headers.map((header) => (
                                            <div key={header} className="grid grid-cols-2 gap-4 items-center">
                                                <div className="font-medium text-sm">{header}</div>
                                                <select
                                                    value={columnMapping[header] || ''}
                                                    onChange={(e) => setColumnMapping({
                                                        ...columnMapping,
                                                        [header]: e.target.value
                                                    })}
                                                    className="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                >
                                                    <option value="">-- Skip this column --</option>
                                                    {leadFields.map((field) => (
                                                        <option key={field.value} value={field.value}>
                                                            {field.label} {field.required && '*'}
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>
                                        ))}
                                    </div>

                                    {/* Import Options */}
                                    <div className="mt-6 space-y-3">
                                        <h4 className="font-medium">Import Options</h4>
                                        <label className="flex items-center">
                                            <input
                                                type="checkbox"
                                                checked={skipDuplicates}
                                                onChange={(e) => setSkipDuplicates(e.target.checked)}
                                                className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            />
                                            <span className="ml-2 text-sm">Skip duplicates (based on email/phone)</span>
                                        </label>
                                        <label className="flex items-center">
                                            <input
                                                type="checkbox"
                                                checked={updateExisting}
                                                onChange={(e) => setUpdateExisting(e.target.checked)}
                                                className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            />
                                            <span className="ml-2 text-sm">Update existing leads if found</span>
                                        </label>
                                    </div>

                                    <div className="mt-6 flex justify-end">
                                        <Button
                                            onClick={handleImport}
                                            disabled={loading || Object.keys(columnMapping).length === 0}
                                            variant="primary"
                                        >
                                            {loading ? 'Importing...' : `Import ${preview.total_rows} Leads`}
                                        </Button>
                                    </div>
                                </div>
                            )}

                            {/* Step 4: Results */}
                            {currentStep === 4 && importResults && (
                                <div className="text-center py-8">
                                    <CheckCircleIcon className="h-16 w-16 text-green-600 mx-auto mb-4" />
                                    <h3 className="text-2xl font-semibold mb-4">Import Complete!</h3>
                                    
                                    <div className="grid grid-cols-3 gap-4 max-w-2xl mx-auto mb-6">
                                        <div className="bg-green-50 p-4 rounded-lg">
                                            <p className="text-3xl font-bold text-green-600">{importResults.imported}</p>
                                            <p className="text-sm text-gray-600">Imported</p>
                                        </div>
                                        <div className="bg-blue-50 p-4 rounded-lg">
                                            <p className="text-3xl font-bold text-blue-600">{importResults.updated}</p>
                                            <p className="text-sm text-gray-600">Updated</p>
                                        </div>
                                        <div className="bg-gray-50 p-4 rounded-lg">
                                            <p className="text-3xl font-bold text-gray-600">{importResults.skipped}</p>
                                            <p className="text-sm text-gray-600">Skipped</p>
                                        </div>
                                    </div>

                                    {importResults.errors && importResults.errors.length > 0 && (
                                        <div className="max-w-2xl mx-auto mb-6 text-left">
                                            <p className="font-medium text-red-600 mb-2">Errors ({importResults.errors.length}):</p>
                                            <ul className="text-sm text-red-600 space-y-1 max-h-40 overflow-y-auto bg-red-50 p-3 rounded">
                                                {importResults.errors.map((error, index) => (
                                                    <li key={index}>• {error}</li>
                                                ))}
                                            </ul>
                                        </div>
                                    )}

                                    <div className="flex justify-center space-x-4">
                                        <Button variant="outline" onClick={resetImport}>
                                            Import More
                                        </Button>
                                        <Button variant="primary" onClick={() => window.location.href = '/leads'}>
                                            View Leads
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}

