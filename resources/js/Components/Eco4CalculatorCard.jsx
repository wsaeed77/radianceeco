import { useState, useEffect } from 'react';
import axios from 'axios';
import Card, { CardHeader, CardTitle, CardContent } from '@/Components/Card';
import Button from '@/Components/Button';

/**
 * ECO4 Calculator Card Component
 * 
 * This is a starter implementation. You can expand it to match CoreLogic's design.
 */
export default function Eco4CalculatorCard({ lead }) {
    const [metadata, setMetadata] = useState(null);
    const [loading, setLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);
    
    // Form state
    const [scheme, setScheme] = useState('GBIS');
    const [sapBand, setSapBand] = useState('');
    const [floorAreaBand, setFloorAreaBand] = useState('');
    const [preMainHeatSource, setPreMainHeatSource] = useState('');
    const [loftMeasureType, setLoftMeasureType] = useState('LI_lessequal100');
    const [selectedMeasures, setSelectedMeasures] = useState([]);
    
    // Load metadata on mount
    useEffect(() => {
        loadMetadata();
        
        // Auto-populate from EPC data if available
        if (lead.epc_data) {
            autoPopulateFromEpc();
        }
    }, [lead]);
    
    const loadMetadata = async () => {
        try {
            const response = await axios.get('/eco4/metadata');
            setMetadata(response.data);
        } catch (err) {
            console.error('Failed to load metadata:', err);
            setError('Failed to load calculator data');
        }
    };
    
    const autoPopulateFromEpc = () => {
        const epc = lead.epc_data;
        
        // Set SAP band from EPC
        if (epc.current_energy_rating) {
            // Determine if High or Low variant based on score
            const score = epc.current_energy_efficiency || 0;
            const band = epc.current_energy_rating;
            const variant = getVariantFromScore(score, band);
            setSapBand(variant);
        }
        
        // Set floor area band from EPC
        if (epc.total_floor_area) {
            const area = parseInt(epc.total_floor_area);
            setFloorAreaBand(getFloorAreaBand(area));
        }
        
        // Set pre-main heating source from EPC
        if (epc.main_heating_description) {
            // Map EPC heating description to database values
            const heatingMap = {
                'boiler and radiators, mains gas': 'Condensing Gas Boiler',
                'condensing gas boiler': 'Condensing Gas Boiler',
                'non condensing gas boiler': 'Non Condensing Gas Boiler',
                'condensing lpg boiler': 'Condensing LPG Boiler',
                'non condensing lpg boiler': 'Non Condensing LPG Boiler',
                'condensing oil boiler': 'Condensing Oil Boiler',
                'non condensing oil boiler': 'Non Condensing Oil Boiler',
                'electric boiler': 'Electric Boiler',
            };
            
            const epcHeating = epc.main_heating_description.toLowerCase();
            const mappedHeating = heatingMap[epcHeating] || 'Condensing Gas Boiler';
            setPreMainHeatSource(mappedHeating);
        } else {
            // Default to most common
            setPreMainHeatSource('Condensing Gas Boiler');
        }
    };
    
    const getVariantFromScore = (score, band) => {
        // Simplified - you might want to refine these ranges
        const ranges = {
            'A': { low: 91.5, mid: 100, high: 110 },
            'B': { low: 80.5, mid: 86, high: 91.4 },
            'C': { low: 68.5, mid: 74.5, high: 80.4 },
            'D': { low: 54.5, mid: 61.5, high: 68.4 },
            'E': { low: 38.5, mid: 46.5, high: 54.4 },
            'F': { low: 20.5, mid: 29.5, high: 38.4 },
            'G': { low: 0, mid: 15.5, high: 20.4 },
        };
        
        if (ranges[band]) {
            return score >= ranges[band].mid ? `High_${band}` : `Low_${band}`;
        }
        return `High_${band}`;
    };
    
    const getFloorAreaBand = (area) => {
        if (area <= 72) return '0-72';
        if (area <= 97) return '73-97';
        if (area <= 199) return '98-199';
        return '200+';
    };
    
    const handleCalculate = async () => {
        if (!sapBand || !floorAreaBand || selectedMeasures.length === 0) {
            setError('Please fill in all required fields and select at least one measure');
            return;
        }
        
        // Only require preMainHeatSource if heating control measures are selected
        const requiresHeatingSource = selectedMeasures.some(m => 
            m.id === 'Smarttherm' || m.id === 'TRV' || m.id === 'P&RT'
        );
        if (requiresHeatingSource && !preMainHeatSource) {
            setError('Please select a pre-main heating source for heating control measures');
            return;
        }
        
        setLoading(true);
        setError(null);
        
        try {
            // Calculate each measure with its specific scheme
            const measureRequests = selectedMeasures.map(async (measure) => {
                const payload = {
                    scheme: measure.scheme, // Use per-measure scheme (GBIS or ECO4)
                    starting_sap_band: sapBand,
                    floor_area_band: floorAreaBand,
                    measures: [{
                        type: measure.id,
                        percentage_treated: 100,
                        is_innovation: false,
                    }],
                };
                
                // Include SAP score if available from EPC (for GBIS Low/High determination)
                if (lead.epc_data && lead.epc_data.current_energy_efficiency) {
                    payload.starting_sap_score = parseInt(lead.epc_data.current_energy_efficiency);
                }
                
                // Only include preMainHeatSource for heating control measures that need it
                if (measure.id === 'Smarttherm' || measure.id === 'TRV' || measure.id === 'P&RT') {
                    payload.pre_main_heat_source = preMainHeatSource;
                }
                
                const response = await axios.post('/eco4/calculate', payload);
                return response.data;
            });
            
            // Wait for all calculations
            const results = await Promise.all(measureRequests);
            
            // Combine all results
            const combinedMeasures = results.flatMap(r => r.measures || []);
            const totalAbs = combinedMeasures.reduce((sum, m) => sum + m.abs_value, 0);
            const totalEcoValue = combinedMeasures.reduce((sum, m) => sum + m.eco_value, 0);
            
            setResult({
                success: true,
                summary: {
                    total_abs: totalAbs,
                    total_eco_value: totalEcoValue,
                    starting_band: sapBand,
                    floor_area_band: floorAreaBand,
                    pps_eco_rate: 21.5,
                },
                measures: combinedMeasures,
            });
        } catch (err) {
            console.error('Calculation error:', err);
            setError(err.response?.data?.message || 'Calculation failed');
        } finally {
            setLoading(false);
        }
    };
    
    const handleSave = async () => {
        if (!result) return;
        
        try {
            await axios.post(`/eco4/leads/${lead.id}/save`, {
                calculation_data: {
                    scheme,
                    starting_sap_band: sapBand,
                    floor_area_band: floorAreaBand,
                    ...result.summary,
                },
                measures: result.measures,
            });
            
            alert('Calculation saved successfully!');
            // Refresh the page to show the saved calculation
            window.location.reload();
        } catch (err) {
            console.error('Save error:', err);
            alert('Failed to save calculation');
        }
    };
    
    if (!metadata) {
        return (
            <Card>
                <CardContent className="p-6 text-center">
                    <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                    <p className="mt-4 text-gray-600">Loading calculator...</p>
                </CardContent>
            </Card>
        );
    }
    
    return (
        <Card>
            <CardHeader className="bg-green-600">
                <CardTitle className="text-white">🧮 ECO4/GBIS Calculator</CardTitle>
            </CardHeader>
            
            <CardContent className="p-6">
                {/* Scheme Selection */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        Scheme
                    </label>
                    <div className="flex gap-4">
                        {metadata.schemes.map(s => (
                            <label key={s} className="flex items-center">
                                <input
                                    type="radio"
                                    value={s}
                                    checked={scheme === s}
                                    onChange={(e) => setScheme(e.target.value)}
                                    className="mr-2"
                                />
                                {s}
                            </label>
                        ))}
                    </div>
                </div>
                
                {/* SAP Band */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        Starting SAP Band
                    </label>
                    <select
                        value={sapBand}
                        onChange={(e) => setSapBand(e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                        <option value="">Select SAP Band...</option>
                        {metadata.sap_bands.map(band => (
                            <optgroup key={band.code} label={`${band.code} (${band.range})`}>
                                {band.variants.map(variant => (
                                    <option key={variant} value={variant}>
                                        {variant.replace('_', ' ')}
                                    </option>
                                ))}
                            </optgroup>
                        ))}
                    </select>
                </div>
                
                {/* Floor Area */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        Floor Area Band (m²)
                    </label>
                    <select
                        value={floorAreaBand}
                        onChange={(e) => setFloorAreaBand(e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                        <option value="">Select Floor Area...</option>
                        {metadata.floor_area_bands.map(band => (
                            <option key={band} value={band}>{band}</option>
                        ))}
                    </select>
                </div>
                
                {/* Loft Insulation Measure Type (only shown if Loft is selected) */}
                {selectedMeasures.some(m => m.id.startsWith('LI_')) && (
                    <div className="mb-4">
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Loft Insulation Measure Type*
                        </label>
                        <select
                            value={loftMeasureType}
                            onChange={(e) => {
                                setLoftMeasureType(e.target.value);
                                // Update the selected measure ID
                                setSelectedMeasures(selectedMeasures.map(m => 
                                    m.id.startsWith('LI_') 
                                        ? { ...m, id: e.target.value }
                                        : m
                                ));
                            }}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md"
                        >
                            <option value="LI_lessequal100">LI_lessequal100 (≤100mm)</option>
                            <option value="LI_greater100">LI_greater100 (&gt;100mm)</option>
                        </select>
                        <p className="mt-1 text-xs text-gray-500">Select based on existing insulation depth</p>
                    </div>
                )}
                
                {/* Pre-main Heating Source (only for heating control measures) */}
                {selectedMeasures.some(m => m.id === 'Smarttherm' || m.id === 'TRV' || m.id === 'P&RT') && (
                    <div className="mb-4">
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Pre-main heating source*
                        </label>
                        <select
                            value={preMainHeatSource}
                            onChange={(e) => setPreMainHeatSource(e.target.value)}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md"
                        >
                            <option value="">Select Heating Source...</option>
                            <option value="Condensing Gas Boiler">Condensing Gas Boiler</option>
                            <option value="Non Condensing Gas Boiler">Non Condensing Gas Boiler</option>
                            <option value="Condensing LPG Boiler">Condensing LPG Boiler</option>
                            <option value="Non Condensing LPG Boiler">Non Condensing LPG Boiler</option>
                            <option value="Condensing Oil Boiler">Condensing Oil Boiler</option>
                            <option value="Non Condensing Oil Boiler">Non Condensing Oil Boiler</option>
                            <option value="Electric Boiler">Electric Boiler</option>
                            <option value="Air to Water ASHP">Air to Water ASHP</option>
                            <option value="GSHP">GSHP</option>
                            <option value="Solid Fossil Boiler">Solid Fossil Boiler</option>
                            <option value="DHS CHP">DHS CHP</option>
                            <option value="DHS non-CHP">DHS non-CHP</option>
                            <option value="Bottled LPG Boiler">Bottled LPG Boiler</option>
                        </select>
                        <p className="mt-1 text-xs text-gray-500">Required for heating control measure calculations</p>
                    </div>
                )}
                
                {/* Select Measures */}
                <div className="mb-6">
                    <label className="block text-base font-semibold text-gray-900 mb-3">
                        Select measures
                    </label>
                    <div className="bg-white border border-gray-300 rounded-lg overflow-hidden">
                        <div>
                            {/* Mixed GBIS + ECO4 measures */}
                            {[
                                { id: 'LI_lessequal100', label: 'Loft Insulation', scheme: 'GBIS', type: 'loft' },
                                { id: 'Smarttherm', label: 'Smart Thermostat', scheme: 'GBIS', type: 'heating' },
                                { id: 'TRV', label: 'TRV', scheme: 'GBIS', type: 'heating' },
                                { id: 'P&RT', label: 'Programmer and Room Thermostat', scheme: 'GBIS', type: 'heating' },
                            ].map((measure) => {
                                const isSelected = selectedMeasures.some(m => {
                                    // For loft, check if any LI_ measure is selected
                                    if (measure.type === 'loft') {
                                        return m.id.startsWith('LI_');
                                    }
                                    return m.id === measure.id;
                                });
                                
                                return (
                                    <label 
                                        key={measure.id}
                                        className="flex items-center justify-between px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors border-b border-gray-100 last:border-b-0"
                                    >
                                        <span className="text-sm font-medium text-gray-800">
                                            {measure.label}
                                        </span>
                                        <input
                                            type="checkbox"
                                            checked={isSelected}
                                            onChange={(e) => {
                                                if (e.target.checked) {
                                                    // When checking Loft, use the current loftMeasureType
                                                    const measureToAdd = measure.type === 'loft' 
                                                        ? { ...measure, id: loftMeasureType }
                                                        : measure;
                                                    setSelectedMeasures([...selectedMeasures, measureToAdd]);
                                                } else {
                                                    // When unchecking, remove all LI_ measures if it's loft
                                                    if (measure.type === 'loft') {
                                                        setSelectedMeasures(selectedMeasures.filter(m => !m.id.startsWith('LI_')));
                                                    } else {
                                                        setSelectedMeasures(selectedMeasures.filter(m => m.id !== measure.id));
                                                    }
                                                }
                                            }}
                                            className="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        />
                                    </label>
                                );
                            })}
                        </div>
                        {/* Done button */}
                        <div className="bg-green-600 hover:bg-green-700 transition-colors">
                            <button
                                type="button"
                                className="w-full px-4 py-3 text-white font-medium flex items-center justify-center"
                                onClick={() => {
                                    // Just close/collapse the measure selection
                                    // The selections are already saved in state
                                }}
                            >
                                Done ✓
                            </button>
                        </div>
                    </div>
                    {selectedMeasures.length > 0 && (
                        <p className="mt-2 text-sm text-gray-600">
                            {selectedMeasures.length} measure{selectedMeasures.length !== 1 ? 's' : ''} selected
                        </p>
                    )}
                </div>
                
                {/* Error Display */}
                {error && (
                    <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
                        {error}
                    </div>
                )}
                
                {/* Calculate Button */}
                <Button
                    onClick={handleCalculate}
                    disabled={loading}
                    variant="primary"
                    className="w-full mb-4"
                >
                    {loading ? 'Calculating...' : 'Calculate'}
                </Button>
                
                {/* Results */}
                {result && result.success && (
                    <div className="mt-6 border-t pt-6">
                        <h3 className="text-lg font-semibold mb-4">Calculation Results</h3>
                        
                        {/* Summary */}
                        <div className="bg-blue-50 p-4 rounded mb-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm text-gray-600">Total ABS</p>
                                    <p className="text-2xl font-bold text-blue-600">
                                        {result.summary.total_abs.toFixed(2)}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600">Total ECO Value</p>
                                    <p className="text-2xl font-bold text-green-600">
                                        £{result.summary.total_eco_value.toFixed(2)}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        {/* Measures */}
                        <div className="space-y-2">
                            <h4 className="font-medium">Measures:</h4>
                            {result.measures.map((measure, idx) => (
                                <div key={idx} className="bg-gray-50 p-3 rounded">
                                    <div className="flex justify-between items-center">
                                        <span className="font-medium">{measure.measure_type}</span>
                                        <span className="text-green-600 font-semibold">
                                            £{measure.eco_value.toFixed(2)}
                                        </span>
                                    </div>
                                    <div className="text-sm text-gray-600 mt-1">
                                        ABS: {measure.abs_value.toFixed(2)} | 
                                        PPS: {measure.pps_points.toFixed(2)}
                                    </div>
                                </div>
                            ))}
                        </div>
                        
                        {/* Save Button */}
                        <Button
                            onClick={handleSave}
                            variant="success"
                            className="w-full mt-4"
                        >
                            Save Calculation to Lead
                        </Button>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

