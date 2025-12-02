import { useState, useEffect } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';
import Card, { CardHeader, CardTitle, CardContent } from '@/Components/Card';
import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import FormInput from '@/Components/FormInput';
import FormSelect from '@/Components/FormSelect';
import FormTextarea from '@/Components/FormTextarea';
import Modal, { ModalHeader, ModalBody, ModalFooter } from '@/Components/Modal';
import AddActivityModal from '@/Components/AddActivityModal';
import AddDocumentModal from '@/Components/AddDocumentModal';
import QuickAddActivityForm from '@/Components/QuickAddActivityForm';
import Eco4CalculatorCard from '@/Components/Eco4CalculatorCard';
import { PencilIcon, DocumentTextIcon, HomeIcon, TrashIcon, FolderIcon, FolderOpenIcon, ChevronRightIcon, ChevronDownIcon, EyeIcon, ArrowDownTrayIcon } from '@heroicons/react/24/outline';
import { formatDate, formatDateTime } from '@/utils';

export default function ShowLead({ lead, activityTypes, documentKinds, epc_certificates }) {
    const [showActivityModal, setShowActivityModal] = useState(false);
    const [showDocumentModal, setShowDocumentModal] = useState(false);
    const [showEpcSelectionModal, setShowEpcSelectionModal] = useState(false);
    const [showExpenseModal, setShowExpenseModal] = useState(false);
    const [showInvoiceModal, setShowInvoiceModal] = useState(false);
    const [epcCertificates, setEpcCertificates] = useState(epc_certificates || []);
    const [epcSearchTerm, setEpcSearchTerm] = useState('');
    const [selectedDocuments, setSelectedDocuments] = useState([]);
    const [bulkDeleteMode, setBulkDeleteMode] = useState(false);
    const [expandedFolders, setExpandedFolders] = useState({});
    const [eprPayments, setEprPayments] = useState(
        Array.isArray(lead?.epr_payments) && lead?.epr_payments.length > 0
            ? lead.epr_payments
            : []
    );
    const [isSavingExpenses, setIsSavingExpenses] = useState(false);
    const [invoiceData, setInvoiceData] = useState({
        buyer_name: '',
        buyer_address: '',
        invoice_date: new Date().toISOString().split('T')[0],
        invoice_no: '',
        order_no: '',
        submission_no: '',
        po_no: '',
        due_date: new Date().toISOString().split('T')[0],
        line_items: [{ details: '', qty: '', price: '', total: '' }]
    });
    const [isSavingInvoice, setIsSavingInvoice] = useState(false);

    // Safety check after hooks
    if (!lead) {
        return (
            <AppLayout>
                <Head title="Lead Not Found" />
                <div className="p-6">Loading...</div>
            </AppLayout>
        );
    }

    // Debug logging
    console.log('Lead data received:', lead);
    console.log('ECO4 calculations:', lead.eco4_calculations);
    console.log('ECO4Calculations (camelCase):', lead.eco4Calculations);

    // Sync expenses with lead data when it updates
    useEffect(() => {
        if (lead.epr_payments && Array.isArray(lead.epr_payments)) {
            // Migrate old expense types when syncing
            const migratedPayments = lead.epr_payments.map(payment => {
                if (payment && payment.type === 'TRV/TTZC') {
                    return { ...payment, type: 'Gas Engineer' };
                }
                if (payment && payment.type === 'Extractor Fan') {
                    return { ...payment, type: 'Loft Material' };
                }
                return payment;
            });
            setEprPayments(migratedPayments);
        } else if (!lead.epr_payments) {
            setEprPayments([]);
        }
    }, [lead.epr_payments]); // Sync when expenses change

    // Check if we have EPC certificates to display in modal
    useEffect(() => {
        if (epc_certificates && epc_certificates.length > 0) {
            setEpcCertificates(epc_certificates);
            setShowEpcSelectionModal(true);
            setEpcSearchTerm(''); // Reset search when opening modal
        }
    }, [epc_certificates]);

    // Group documents by kind
    const groupDocumentsByKind = () => {
        if (!lead.documents || lead.documents.length === 0) return {};
        
        const grouped = {};
        lead.documents.forEach(doc => {
            const kind = doc.kind || 'other';
            if (!grouped[kind]) {
                grouped[kind] = [];
            }
            grouped[kind].push(doc);
        });
        return grouped;
    };

    // Format document kind label
    const formatDocumentKind = (kind) => {
        const labels = {
            'survey_pics': 'Survey Pictures',
            'floor_plan': 'Floor Plan',
            'benefit_proof': 'Benefit Proof',
            'gas_meter': 'Gas Meter',
            'epr_report': 'EPR Report',
            'epc': 'EPC',
            'other': 'Other Documents'
        };
        return labels[kind] || kind.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    };

    // Check if document can be viewed in browser (images or PDFs)
    const canViewDocument = (document) => {
        const fileName = document.name.toLowerCase();
        const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp', '.svg'];
        const pdfExtension = '.pdf';
        
        return imageExtensions.some(ext => fileName.endsWith(ext)) || fileName.endsWith(pdfExtension);
    };

    // Toggle folder expansion
    const toggleFolder = (kind) => {
        setExpandedFolders(prev => ({
            ...prev,
            [kind]: !prev[kind]
        }));
    };

    const handleDelete = () => {
        if (confirm(`Are you sure you want to delete ${lead.first_name} ${lead.last_name}? This action cannot be undone.`)) {
            router.delete(route('leads.destroy', lead.id));
        }
    };

    const handleSelectEpcCertificate = (certificate) => {
        router.post(route('epc.save', lead.id), {
            certificate_data: JSON.stringify(certificate),
        }, {
            onSuccess: () => {
                setShowEpcSelectionModal(false);
                setEpcCertificates([]);
            }
        });
    };

    // EPR Payment handlers
    const addEprPayment = () => {
        console.log('Adding new expense, current count:', eprPayments.length);
        const newPayment = { type: '', amount: '', quantity: '', rate: '', percentage: '', other_details: '' };
        const updated = [...eprPayments, newPayment];
        setEprPayments(updated);
        console.log('Updated expenses:', updated);
    };

    const handleOpenExpenseModal = () => {
        console.log('Opening expense modal...');
        console.log('Current lead.epr_payments:', lead.epr_payments);
        // Sync expenses from lead data when opening modal
        if (lead.epr_payments && Array.isArray(lead.epr_payments)) {
            // Migrate old expense types
            const migratedPayments = lead.epr_payments.map(payment => {
                if (payment.type === 'TRV/TTZC') {
                    return { ...payment, type: 'Gas Engineer' };
                }
                if (payment.type === 'Extractor Fan') {
                    return { ...payment, type: 'Loft Material' };
                }
                return payment;
            });
            setEprPayments(migratedPayments);
            console.log('Synced expenses from lead:', migratedPayments);
        } else {
            setEprPayments([]);
            console.log('No expenses found, starting with empty array');
        }
        setShowExpenseModal(true);
        console.log('Modal state set to true');
    };

    const handleCloseExpenseModal = () => {
        setShowExpenseModal(false);
    };

    const removeEprPayment = (index) => {
        const newPayments = eprPayments.filter((_, i) => i !== index);
        setEprPayments(newPayments);
    };

    const updateEprPayment = (index, field, value) => {
        if (index < 0 || index >= eprPayments.length) return;
        
        const newPayments = [...eprPayments];
        if (!newPayments[index]) {
            newPayments[index] = { type: '', amount: '', quantity: '', rate: '', percentage: '', other_details: '' };
        }
        
        newPayments[index] = {
            ...newPayments[index],
            [field]: value
        };
        
        // Calculate total for Airbox (quantity * fixed rate £12.50)
        if (newPayments[index].type === 'Airbox') {
            const qty = parseFloat(newPayments[index].quantity || 0) || 0;
            const fixedRate = 12.50;
            newPayments[index].rate = fixedRate.toFixed(2);
            newPayments[index].amount = (qty * fixedRate).toFixed(2);
        }
        
        // Calculate VAT based on percentage of other expenses
        if (newPayments[index].type === 'VAT') {
            const percentage = parseFloat(newPayments[index].percentage || 0) || 0;
            // Calculate total of all non-VAT expenses
            const otherExpenses = newPayments
                .filter((p, i) => i !== index && p && p.type && p.type !== 'VAT' && p.amount)
                .reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
            newPayments[index].amount = ((otherExpenses * percentage) / 100).toFixed(2);
        }
        
        // For other types, ensure amount is set if it's empty
        if (newPayments[index].type && newPayments[index].type !== 'Airbox' && newPayments[index].type !== 'VAT' && !newPayments[index].amount) {
            newPayments[index].amount = '0';
        }
        
        setEprPayments(newPayments);
    };

    const saveExpenses = () => {
        console.log('Saving expenses...');
        console.log('Current eprPayments:', eprPayments);
        
        // Filter out incomplete expenses (no type or no amount)
        const validPayments = eprPayments.filter(payment => {
            if (!payment) return false;
            const hasType = payment.type && payment.type.trim() !== '';
            
            if (!hasType) return false;
            
            // For VAT, amount is calculated automatically
            if (payment.type === 'VAT') {
                // VAT needs percentage to calculate amount
                return payment.percentage && parseFloat(payment.percentage || 0) > 0;
            }
            
            // For Airbox, amount is calculated automatically from quantity
            if (payment.type === 'Airbox') {
                return payment.quantity && parseFloat(payment.quantity || 0) > 0;
            }
            
            // For DMEV, need quantity and amount
            if (payment.type === 'DMEV') {
                const hasQuantity = payment.quantity && parseFloat(payment.quantity || 0) > 0;
                const amount = parseFloat(payment.amount || 0);
                return hasQuantity && !isNaN(amount) && amount > 0;
            }
            
            // For "Remedial" and "Other" types, need description and amount
            if (payment.type === 'Remedial' || payment.type === 'Other') {
                const hasDetails = payment.other_details && payment.other_details.trim() !== '';
                const amount = parseFloat(payment.amount || 0);
                return hasDetails && !isNaN(amount) && amount > 0;
            }
            
            // For other types, need amount
            const amount = parseFloat(payment.amount || 0);
            return !isNaN(amount) && amount > 0;
        });

        console.log('Valid payments:', validPayments);

        if (validPayments.length === 0 && eprPayments.length > 0) {
            alert('Please complete at least one expense entry (Type and Amount are required).');
            return;
        }

        setIsSavingExpenses(true);
        
        // Clean up the data structure
        const cleanedPayments = validPayments.map(payment => ({
            type: payment.type || '',
            amount: payment.amount || '0',
            quantity: payment.quantity || '',
            rate: payment.rate || '',
            percentage: payment.percentage || '',
            other_details: payment.other_details || ''
        }));

        console.log('Cleaned payments to save:', cleanedPayments);
        console.log('Saving to route:', route('leads.update', lead.id));

        // Include required fields from lead to pass validation
        const statusId = lead.status_id || (lead.status_model && lead.status_model.id) || '';
        const zipCode = lead.zip_code || lead.postcode || '';
        const stage = lead.stage || '';
        
        console.log('Including required fields:', { zip_code: zipCode, status_id: statusId, stage: stage });

        router.put(route('leads.update', lead.id), {
            epr_payments: cleanedPayments,
            zip_code: zipCode,
            status_id: statusId,
            stage: stage,
        }, {
            preserveScroll: true,
            onStart: () => {
                console.log('Request started...');
            },
            onSuccess: (page) => {
                console.log('Success! Updated page data:', page);
                setIsSavingExpenses(false);
                setShowExpenseModal(false);
                // The page will automatically refresh with updated lead data via Inertia
            },
            onError: (errors) => {
                console.error('Error saving expenses:', errors);
                setIsSavingExpenses(false);
                if (errors.epr_payments) {
                    alert('Error saving expenses: ' + (Array.isArray(errors.epr_payments) ? errors.epr_payments.join(', ') : errors.epr_payments));
                } else {
                    alert('Error saving expenses. Please check the console for details.');
                }
            }
        });
    };

    // Invoice handlers
    const handleOpenInvoiceModal = () => {
        setInvoiceData({
            buyer_name: '',
            buyer_address: '',
            invoice_date: new Date().toISOString().split('T')[0],
            invoice_no: '',
            order_no: '',
            submission_no: '',
            po_no: '',
            due_date: new Date().toISOString().split('T')[0],
            line_items: [{ details: '', qty: '', price: '', total: '' }]
        });
        setShowInvoiceModal(true);
    };

    const handleCloseInvoiceModal = () => {
        setShowInvoiceModal(false);
    };

    const addInvoiceLineItem = () => {
        setInvoiceData(prev => ({
            ...prev,
            line_items: [...prev.line_items, { details: '', qty: '', price: '', total: '' }]
        }));
    };

    const removeInvoiceLineItem = (index) => {
        setInvoiceData(prev => ({
            ...prev,
            line_items: prev.line_items.filter((_, i) => i !== index)
        }));
    };

    const updateInvoiceLineItem = (index, field, value) => {
        setInvoiceData(prev => {
            const newLineItems = [...prev.line_items];
            newLineItems[index] = {
                ...newLineItems[index],
                [field]: value
            };
            
            // Calculate total if qty and price are provided
            if (field === 'qty' || field === 'price') {
                const qty = parseFloat(newLineItems[index].qty || 0);
                const price = parseFloat(newLineItems[index].price || 0);
                newLineItems[index].total = (qty * price).toFixed(2);
            }
            
            return {
                ...prev,
                line_items: newLineItems
            };
        });
    };

    const updateInvoiceField = (field, value) => {
        setInvoiceData(prev => ({
            ...prev,
            [field]: value
        }));
    };

    const saveInvoice = () => {
        // Validate required fields
        if (!invoiceData.buyer_name || !invoiceData.buyer_address || !invoiceData.invoice_date || !invoiceData.invoice_no) {
            alert('Please fill in Buyer Name, Buyer Address, Invoice Date and Invoice No.');
            return;
        }

        // Validate line items (allow negative prices for credits/adjustments)
        const validLineItems = invoiceData.line_items.filter(item => 
            item.details && item.details.trim() !== '' && 
            item.qty && parseFloat(item.qty) !== 0 && 
            item.price !== '' && item.price !== null && !isNaN(parseFloat(item.price))
        );

        if (validLineItems.length === 0) {
            alert('Please add at least one valid line item.');
            return;
        }

        setIsSavingInvoice(true);

        // Format line items for submission
        const formattedLineItems = validLineItems.map(item => ({
            details: item.details.trim(),
            qty: parseFloat(item.qty),
            price: parseFloat(item.price),
            total: parseFloat(item.total || (parseFloat(item.qty) * parseFloat(item.price)).toFixed(2))
        }));

        // Use route helper, fallback to direct URL if route not available
        let invoiceRoute;
        try {
            invoiceRoute = route('invoices.store', lead.id);
        } catch (error) {
            // Fallback to direct URL if Ziggy route not available
            console.warn('Route helper not available, using direct URL');
            invoiceRoute = `/leads/${lead.id}/invoices`;
        }

        router.post(invoiceRoute, {
            ...invoiceData,
            line_items: formattedLineItems
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setIsSavingInvoice(false);
                setShowInvoiceModal(false);
            },
            onError: (errors) => {
                setIsSavingInvoice(false);
                console.error('Error saving invoice:', errors);
                alert('Error saving invoice. Please check the console for details.');
            }
        });
    };

    // Bulk delete functions
    const handleSelectDocument = (documentId) => {
        setSelectedDocuments(prev => 
            prev.includes(documentId) 
                ? prev.filter(id => id !== documentId)
                : [...prev, documentId]
        );
    };

    const handleSelectAllDocuments = (documents) => {
        if (selectedDocuments.length === documents.length) {
            setSelectedDocuments([]);
        } else {
            setSelectedDocuments(documents.map(doc => doc.id));
        }
    };

    const handleBulkDelete = () => {
        if (selectedDocuments.length === 0) return;
        
        if (confirm(`Are you sure you want to delete ${selectedDocuments.length} document(s)?`)) {
            router.post(route('documents.bulk-delete'), {
                document_ids: selectedDocuments
            }, {
                onSuccess: () => {
                    setSelectedDocuments([]);
                    setBulkDeleteMode(false);
                }
            });
        }
    };

    const handleDeleteCalculation = (calculationId) => {
        if (confirm('Are you sure you want to delete this calculation? This action cannot be undone.')) {
            router.delete(`/eco4/calculations/${calculationId}`, {
                onSuccess: () => {
                    // Refresh the page to show updated calculations
                    window.location.reload();
                },
                onError: (errors) => {
                    console.error('Error deleting calculation:', errors);
                    alert('Failed to delete calculation. Please try again.');
                }
            });
        }
    };

    const toggleBulkDeleteMode = () => {
        setBulkDeleteMode(!bulkDeleteMode);
        setSelectedDocuments([]);
    };
    const getStatusBadge = (status, statusLabel, statusColor) => {
        // Use the color from the status model if available, otherwise fallback to default
        const colorMap = {
            'primary': 'primary',
            'secondary': 'secondary', 
            'success': 'success',
            'danger': 'danger',
            'warning': 'warning',
            'info': 'info',
        };
        const variant = colorMap[statusColor] || 'default';
        return <Badge variant={variant}>{statusLabel}</Badge>;
    };

    const getDataMatchBadge = (status) => {
        const variants = {
            'Matched': 'success',
            'Pending': 'warning',
            'Sent': 'info',
            'Unmatched': 'danger',
        };
        return <Badge variant={variants[status] || 'default'}>{status}</Badge>;
    };

    const getEPCBadge = (rating) => {
        const variants = {
            'A': 'success',
            'B': 'success',
            'C': 'info',
            'D': 'info',
            'E': 'warning',
            'F': 'danger',
        };
        return <Badge variant={variants[rating] || 'default'}>{rating}</Badge>;
    };

    // Map numeric score (0-100) to EPC-like band label used by EPR
    const getLabelFromScore = (score) => {
        const n = parseInt(score, 10);
        if (isNaN(n)) return null;
        if (n >= 92) return 'A';
        if (n >= 81) return 'B';
        if (n >= 80) return 'High C';
        if (n >= 69) return 'Low C';
        if (n >= 68) return 'High D';
        if (n >= 55) return 'Low D';
        if (n >= 39) return 'High E';
        if (n >= 21) return 'Low E';
        if (n >= 1) return 'G';
        return null;
    };

    return (
        <AppLayout>
            <Head title={`${lead.first_name} ${lead.last_name}`} />

            <PageHeader
                title="Lead Details"
                breadcrumbs={[
                    { label: 'Leads', href: route('leads.index') },
                    { label: `${lead.first_name} ${lead.last_name}` },
                ]}
                actions={
                    <div className="flex gap-2">
                        <Link href={route('leads.edit', lead.id)}>
                            <Button variant="primary" size="sm">
                                <PencilIcon className="-ml-1 mr-2 h-4 w-4" />
                                Edit Lead
                            </Button>
                        </Link>
                        <Button variant="danger" size="sm" onClick={handleDelete}>
                            <TrashIcon className="-ml-1 mr-2 h-4 w-4" />
                            Delete Lead
                        </Button>
                        <Link href={route('leads.index')}>
                            <Button variant="secondary" size="sm">Back to Leads</Button>
                        </Link>
                        <Link href={route('dashboard')}>
                            <Button variant="secondary" size="sm">
                                <HomeIcon className="-ml-1 mr-2 h-4 w-4" />
                                Dashboard
                            </Button>
                        </Link>
                    </div>
                }
            />

            {/* Lead Information and Additional Info - Two Column */}
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
                {/* Lead Information */}
                <Card padding={false}>
                    <CardHeader className="bg-primary-600">
                        <CardTitle className="text-white">Lead Information</CardTitle>
                    </CardHeader>
                    <CardContent className="p-6">
                        <dl className="space-y-3">
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Name:</dt>
                                <dd className="col-span-2 text-gray-900">{lead.first_name} {lead.last_name}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Email:</dt>
                                <dd className="col-span-2 text-gray-900">{lead.email || 'N/A'}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Phone:</dt>
                                <dd className="col-span-2 text-gray-900">{lead.phone || 'N/A'}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Status:</dt>
                                <dd className="col-span-2">{getStatusBadge(lead.status_model?.name || lead.status, lead.status_model?.name || lead.status_label, lead.status_model?.color)}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Team:</dt>
                                <dd className="col-span-2"><Badge variant="default">{lead.stage_label}</Badge></dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Address:</dt>
                                <dd className="col-span-2 text-gray-900">
                                    {lead.address_line_1 ? (
                                        <>
                                            {lead.address_line_1}<br />
                                            {lead.address_line_2 && <>{lead.address_line_2}<br /></>}
                                            {lead.city} {lead.zip_code}
                                        </>
                                    ) : 'N/A'}
                                </dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Created:</dt>
                                <dd className="col-span-2 text-gray-900">{formatDateTime(lead.created_at)}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Last Updated:</dt>
                                <dd className="col-span-2 text-gray-900">{formatDateTime(lead.updated_at)}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                {/* Additional Information */}
                <Card padding={false}>
                    <CardHeader className="bg-blue-500">
                        <CardTitle className="text-white">Additional Information</CardTitle>
                    </CardHeader>
                    <CardContent className="p-6">
                        <dl className="space-y-3">
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Source:</dt>
                                <dd className="col-span-2 text-gray-900">{lead.source || 'N/A'}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Source Details:</dt>
                                <dd className="col-span-2 text-gray-900">{lead.source_details || 'N/A'}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Grant Type:</dt>
                                <dd className="col-span-2 text-gray-900">{lead.grant_type || 'N/A'}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Funders:</dt>
                                <dd className="col-span-2 text-gray-900">{lead.funders || 'N/A'}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Is Duplicate:</dt>
                                <dd className="col-span-2 text-gray-900">{lead.is_duplicate ? 'Yes' : 'No'}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Is Complete:</dt>
                                <dd className="col-span-2 text-gray-900">{lead.is_complete ? 'Yes' : 'No'}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Notes:</dt>
                                <dd className="col-span-2 text-gray-900">{lead.notes || 'No notes available'}</dd>
                            </div>
                            <div className="grid grid-cols-3 gap-4">
                                <dt className="font-semibold text-gray-700">Assigned Agent:</dt>
                                <dd className="col-span-2 text-gray-900">
                                    {lead.assignedAgent ? lead.assignedAgent.name : 'Not Assigned'}
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>
            </div>

            {/* Data Match Section - Full Width */}
            <Card padding={false} className="mb-6">
                <CardHeader className="bg-purple-600">
                    <CardTitle className="text-white">📊 Data Match</CardTitle>
                </CardHeader>
                <CardContent className="p-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dl className="space-y-3">
                                <div className="grid grid-cols-5 gap-4">
                                    <dt className="col-span-2 font-semibold text-gray-700">Benefit Holder Name:</dt>
                                    <dd className="col-span-3 text-gray-900">{lead.benefit_holder_name || 'Not specified'}</dd>
                                </div>
                                <div className="grid grid-cols-5 gap-4">
                                    <dt className="col-span-2 font-semibold text-gray-700">Benefit Holder DOB:</dt>
                                    <dd className="col-span-3 text-gray-900">
                                        {lead.benefit_holder_dob ? formatDate(lead.benefit_holder_dob) : 'Not specified'}
                                    </dd>
                                </div>
                                <div className="grid grid-cols-5 gap-4">
                                    <dt className="col-span-2 font-semibold text-gray-700">Data Match Status:</dt>
                                    <dd className="col-span-3">
                                        {lead.data_match_status ? getDataMatchBadge(lead.data_match_status) : (
                                            <span className="text-gray-500">Not specified</span>
                                        )}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <dl className="space-y-3">
                                <div className="grid grid-cols-5 gap-4">
                                    <dt className="col-span-2 font-semibold text-gray-700">Phone Numbers:</dt>
                                    <dd className="col-span-3">
                                        {lead.multi_phone_numbers && lead.multi_phone_numbers.length > 0 ? (
                                            <ul className="space-y-1">
                                                {lead.multi_phone_numbers.map((phone, index) => (
                                                    <li key={index} className="text-sm text-gray-900">
                                                        <strong>{phone.label || 'Phone'}:</strong> {phone.number}
                                                    </li>
                                                ))}
                                            </ul>
                                        ) : (
                                            <span className="text-gray-500">No phone numbers added</span>
                                        )}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    
                    <div className="mt-4 p-4 bg-gray-50 rounded-lg">
                        <div className="grid grid-cols-4 gap-4">
                            <dt className="font-semibold text-gray-700">Data Match Remarks:</dt>
                            <dd className="col-span-3 text-gray-900">{lead.data_match_remarks || 'Not specified'}</dd>
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* Eligibility Details - Full Width */}
            <Card padding={false} className="mb-6">
                <CardHeader className="bg-success-600">
                    <CardTitle className="text-white">✓ Eligibility Details</CardTitle>
                </CardHeader>
                <CardContent className="p-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dl className="space-y-3">
                                <div className="grid grid-cols-5 gap-4">
                                    <dt className="col-span-2 font-semibold text-gray-700">Occupancy Type:</dt>
                                    <dd className="col-span-3">
                                        {lead.occupancy_type ? (
                                            <Badge variant={lead.occupancy_type === 'owner' ? 'primary' : 'info'}>
                                                {lead.occupancy_type.charAt(0).toUpperCase() + lead.occupancy_type.slice(1)}
                                            </Badge>
                                        ) : (
                                            <span className="text-gray-500">Not specified</span>
                                        )}
                                    </dd>
                                </div>
                                <div className="grid grid-cols-5 gap-4">
                                    <dt className="col-span-2 font-semibold text-gray-700">Client DOB:</dt>
                                    <dd className="col-span-3 text-gray-900">
                                        {lead.eligibility_client_dob ? formatDate(lead.eligibility_client_dob) : 'Not specified'}
                                    </dd>
                                </div>
                                <div className="grid grid-cols-5 gap-4">
                                    <dt className="col-span-2 font-semibold text-gray-700">Possible Grant:</dt>
                                    <dd className="col-span-3 text-gray-900">{lead.possible_grant_types || 'Not specified'}</dd>
                                </div>
                                <div className="grid grid-cols-5 gap-4">
                                    <dt className="col-span-2 font-semibold text-gray-700">Benefit:</dt>
                                    <dd className="col-span-3 text-gray-900">{lead.benefit_type || 'Not specified'}</dd>
                                </div>
                                <div className="grid grid-cols-5 gap-4">
                                    <dt className="col-span-2 font-semibold text-gray-700">Proof of Address:</dt>
                                    <dd className="col-span-3 text-gray-900">{lead.poa_info || 'Not specified'}</dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <dl className="space-y-3">
                                <div className="grid grid-cols-5 gap-4">
                                    <dt className="col-span-2 font-semibold text-gray-700">EPC Rating:</dt>
                                    <dd className="col-span-3">
                                        {lead.epc_rating ? getEPCBadge(lead.epc_rating) : (
                                            <span className="text-gray-500">Not specified</span>
                                        )}
                                    </dd>
                                </div>
                                <div className="grid grid-cols-5 gap-4">
                                    <dt className="col-span-2 font-semibold text-gray-700">EPC Details:</dt>
                                    <dd className="col-span-3 text-gray-900">{lead.epc_details || 'Not specified'}</dd>
                                </div>
                                <div className="grid grid-cols-5 gap-4">
                                    <dt className="col-span-2 font-semibold text-gray-700">GAS SAFE:</dt>
                                    <dd className="col-span-3 text-gray-900">{lead.gas_safe_info || 'Not specified'}</dd>
                                </div>
                                <div className="grid grid-cols-5 gap-4">
                                    <dt className="col-span-2 font-semibold text-gray-700">Council Tax Band:</dt>
                                    <dd className="col-span-3">
                                        {lead.council_tax_band ? (
                                            <Badge variant="default">Band {lead.council_tax_band}</Badge>
                                        ) : (
                                            <span className="text-gray-500">Not specified</span>
                                        )}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* EPC Report Section */}
            <Card padding={false} className="mb-6">
                <CardHeader className="bg-green-600">
                    <div className="flex items-center justify-between">
                        <CardTitle className="text-white">🏠 EPC (Energy Performance Certificate)</CardTitle>
                        <div className="flex gap-2">
                            {lead.epc_data && (
                                <form onSubmit={(e) => {
                                    e.preventDefault();
                                    if (confirm('This will clear the current EPC data. Are you sure?')) {
                                        router.delete(route('epc.clear', lead.id));
                                    }
                                }}>
                                    <Button variant="danger" size="sm" type="submit">
                                        Clear EPC Data
                                    </Button>
                                </form>
                            )}
                            <form onSubmit={(e) => {
                                e.preventDefault();
                                router.post(route('epc.fetch', lead.id));
                            }}>
                                <Button variant="primary" size="sm" type="submit">
                                    {lead.epc_data ? 'Refresh' : 'Fetch'} EPC Report
                                </Button>
                            </form>
                            {lead.epc_data && (
                                <form onSubmit={(e) => {
                                    e.preventDefault();
                                    router.post(route('epc.recommendations', lead.id));
                                }}>
                                    <Button variant="secondary" size="sm" type="submit">
                                        {lead.epc_recommendations ? 'Refresh' : 'Fetch'} Recommendations
                                    </Button>
                                </form>
                            )}
                        </div>
                    </div>
                    {lead.epc_fetched_at && (
                        <p className="text-white text-sm mt-2">
                            Last fetched: {formatDateTime(lead.epc_fetched_at)}
                        </p>
                    )}
                </CardHeader>
                <CardContent className="p-6">
                    {lead.epc_data ? (
                        <div className="space-y-6">
                            {/* Energy Rating Overview */}
                            <div className="bg-gradient-to-r from-green-50 to-blue-50 p-6 rounded-lg border-2 border-green-200">
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div className="text-center">
                                        <h3 className="text-sm font-semibold text-gray-600 mb-2">Current Energy Rating</h3>
                                        <div className={`inline-flex items-center justify-center w-20 h-20 rounded-full text-4xl font-bold ${
                                            lead.epc_data.current_energy_rating === 'A' || lead.epc_data.current_energy_rating === 'B' ? 'bg-green-500 text-white' :
                                            lead.epc_data.current_energy_rating === 'C' || lead.epc_data.current_energy_rating === 'D' ? 'bg-yellow-500 text-white' :
                                            'bg-red-500 text-white'
                                        }`}>
                                            {lead.epc_data.current_energy_rating || 'N/A'}
                                        </div>
                                        <p className="text-2xl font-bold text-gray-900 mt-2">{lead.epc_data.current_energy_efficiency || 0}</p>
                                        <p className="text-xs text-gray-600">Energy Score</p>
                                    </div>
                                    
                                    <div className="text-center">
                                        <h3 className="text-sm font-semibold text-gray-600 mb-2">Potential Rating</h3>
                                        <div className={`inline-flex items-center justify-center w-20 h-20 rounded-full text-4xl font-bold ${
                                            lead.epc_data.potential_energy_rating === 'A' || lead.epc_data.potential_energy_rating === 'B' ? 'bg-green-500 text-white' :
                                            lead.epc_data.potential_energy_rating === 'C' || lead.epc_data.potential_energy_rating === 'D' ? 'bg-yellow-500 text-white' :
                                            'bg-red-500 text-white'
                                        }`}>
                                            {lead.epc_data.potential_energy_rating || 'N/A'}
                                        </div>
                                        <p className="text-2xl font-bold text-gray-900 mt-2">{lead.epc_data.potential_energy_efficiency || 0}</p>
                                        <p className="text-xs text-gray-600">Potential Score</p>
                                    </div>
                                    
                                    <div className="text-center">
                                        <h3 className="text-sm font-semibold text-gray-600 mb-2">Certificate Details</h3>
                                        <p className="text-sm text-gray-700 mt-4">
                                            <strong>Property:</strong> {lead.epc_data.property_type || 'N/A'}
                                        </p>
                                        <p className="text-sm text-gray-700">
                                            <strong>Floor Area:</strong> {lead.epc_data.total_floor_area || 'N/A'} m²
                                        </p>
                                        <p className="text-sm text-gray-700">
                                            <strong>Construction:</strong> {lead.epc_data.construction_age_band || 'N/A'}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Property Features */}
                            <div>
                                <h3 className="text-lg font-bold text-gray-900 mb-4">Features in this Property</h3>
                                <p className="text-sm text-gray-600 mb-4">
                                    Features get a rating from very good to very poor, based on how energy efficient they are. 
                                    Ratings are not based on how well features work or their condition.
                                </p>
                                
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feature</th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {lead.epc_data.walls_description && (
                                                <tr>
                                                    <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">Wall</td>
                                                    <td className="px-6 py-4 text-sm text-gray-700">{lead.epc_data.walls_description}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <Badge variant={
                                                            lead.epc_data.walls_energy_eff === 'Very Good' ? 'success' :
                                                            lead.epc_data.walls_energy_eff === 'Good' ? 'primary' :
                                                            lead.epc_data.walls_energy_eff === 'Average' ? 'warning' :
                                                            lead.epc_data.walls_energy_eff === 'Poor' || lead.epc_data.walls_energy_eff === 'Very Poor' ? 'danger' :
                                                            'default'
                                                        }>
                                                            {lead.epc_data.walls_energy_eff || 'N/A'}
                                                        </Badge>
                                                    </td>
                                                </tr>
                                            )}
                                            {lead.epc_data.roof_description && (
                                                <tr>
                                                    <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">Roof</td>
                                                    <td className="px-6 py-4 text-sm text-gray-700">{lead.epc_data.roof_description}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <Badge variant={
                                                            lead.epc_data.roof_energy_eff === 'Very Good' ? 'success' :
                                                            lead.epc_data.roof_energy_eff === 'Good' ? 'primary' :
                                                            lead.epc_data.roof_energy_eff === 'Average' ? 'warning' :
                                                            lead.epc_data.roof_energy_eff === 'Poor' || lead.epc_data.roof_energy_eff === 'Very Poor' ? 'danger' :
                                                            'default'
                                                        }>
                                                            {lead.epc_data.roof_energy_eff || 'N/A'}
                                                        </Badge>
                                                    </td>
                                                </tr>
                                            )}
                                            {lead.epc_data.windows_description && (
                                                <tr>
                                                    <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">Window</td>
                                                    <td className="px-6 py-4 text-sm text-gray-700">{lead.epc_data.windows_description}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <Badge variant={
                                                            lead.epc_data.windows_energy_eff === 'Very Good' ? 'success' :
                                                            lead.epc_data.windows_energy_eff === 'Good' ? 'primary' :
                                                            lead.epc_data.windows_energy_eff === 'Average' ? 'warning' :
                                                            lead.epc_data.windows_energy_eff === 'Poor' || lead.epc_data.windows_energy_eff === 'Very Poor' ? 'danger' :
                                                            'default'
                                                        }>
                                                            {lead.epc_data.windows_energy_eff || 'N/A'}
                                                        </Badge>
                                                    </td>
                                                </tr>
                                            )}
                                            {lead.epc_data.main_heating_description && (
                                                <tr>
                                                    <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">Main Heating</td>
                                                    <td className="px-6 py-4 text-sm text-gray-700">{lead.epc_data.main_heating_description}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <Badge variant={
                                                            lead.epc_data.main_heating_energy_eff === 'Very Good' ? 'success' :
                                                            lead.epc_data.main_heating_energy_eff === 'Good' ? 'primary' :
                                                            lead.epc_data.main_heating_energy_eff === 'Average' ? 'warning' :
                                                            lead.epc_data.main_heating_energy_eff === 'Poor' || lead.epc_data.main_heating_energy_eff === 'Very Poor' ? 'danger' :
                                                            'default'
                                                        }>
                                                            {lead.epc_data.main_heating_energy_eff || 'N/A'}
                                                        </Badge>
                                                    </td>
                                                </tr>
                                            )}
                                            {lead.epc_data.main_heating_controls && (
                                                <tr>
                                                    <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">Main Heating Control</td>
                                                    <td className="px-6 py-4 text-sm text-gray-700">{lead.epc_data.main_heating_controls}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <Badge variant={
                                                            lead.epc_data.main_heating_controls_energy_eff === 'Very Good' ? 'success' :
                                                            lead.epc_data.main_heating_controls_energy_eff === 'Good' ? 'primary' :
                                                            lead.epc_data.main_heating_controls_energy_eff === 'Average' ? 'warning' :
                                                            lead.epc_data.main_heating_controls_energy_eff === 'Poor' || lead.epc_data.main_heating_controls_energy_eff === 'Very Poor' ? 'danger' :
                                                            'default'
                                                        }>
                                                            {lead.epc_data.main_heating_controls_energy_eff || 'N/A'}
                                                        </Badge>
                                                    </td>
                                                </tr>
                                            )}
                                            {lead.epc_data.hot_water_description && (
                                                <tr>
                                                    <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">Hot Water</td>
                                                    <td className="px-6 py-4 text-sm text-gray-700">{lead.epc_data.hot_water_description}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <Badge variant={
                                                            lead.epc_data.hot_water_energy_eff === 'Very Good' ? 'success' :
                                                            lead.epc_data.hot_water_energy_eff === 'Good' ? 'primary' :
                                                            lead.epc_data.hot_water_energy_eff === 'Average' ? 'warning' :
                                                            lead.epc_data.hot_water_energy_eff === 'Poor' || lead.epc_data.hot_water_energy_eff === 'Very Poor' ? 'danger' :
                                                            'default'
                                                        }>
                                                            {lead.epc_data.hot_water_energy_eff || 'N/A'}
                                                        </Badge>
                                                    </td>
                                                </tr>
                                            )}
                                            {lead.epc_data.lighting_description && (
                                                <tr>
                                                    <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">Lighting</td>
                                                    <td className="px-6 py-4 text-sm text-gray-700">{lead.epc_data.lighting_description}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <Badge variant={
                                                            lead.epc_data.lighting_energy_eff === 'Very Good' ? 'success' :
                                                            lead.epc_data.lighting_energy_eff === 'Good' ? 'primary' :
                                                            lead.epc_data.lighting_energy_eff === 'Average' ? 'warning' :
                                                            lead.epc_data.lighting_energy_eff === 'Poor' || lead.epc_data.lighting_energy_eff === 'Very Poor' ? 'danger' :
                                                            'default'
                                                        }>
                                                            {lead.epc_data.lighting_energy_eff || 'N/A'}
                                                        </Badge>
                                                    </td>
                                                </tr>
                                            )}
                                            {lead.epc_data.floor_description && (
                                                <tr>
                                                    <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">Floor</td>
                                                    <td className="px-6 py-4 text-sm text-gray-700">{lead.epc_data.floor_description}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <Badge variant="default">
                                                            {lead.epc_data.floor_energy_eff || 'N/A'}
                                                        </Badge>
                                                    </td>
                                                </tr>
                                            )}
                                            {lead.epc_data.secondheat_description && lead.epc_data.secondheat_description !== 'None' && (
                                                <tr>
                                                    <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">Secondary Heating</td>
                                                    <td className="px-6 py-4 text-sm text-gray-700">{lead.epc_data.secondheat_description}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <Badge variant="default">N/A</Badge>
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {/* Recommendations */}
                            {lead.epc_recommendations && lead.epc_recommendations.length > 0 && (
                                <div>
                                    <h3 className="text-lg font-bold text-gray-900 mb-4">Recommended Improvements</h3>
                                    <div className="overflow-x-auto">
                                        <table className="min-w-full divide-y divide-gray-200">
                                            <thead className="bg-gray-50">
                                                <tr>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Measure</th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estimated Cost</th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Typical Saving</th>
                                                </tr>
                                            </thead>
                                            <tbody className="bg-white divide-y divide-gray-200">
                                                {lead.epc_recommendations.map((rec, idx) => {
                                                    const measure = rec['improvement-summary-text'] || rec['improvement-descr-text'] || rec.suggestion || rec.measure || '—';
                                                    const category = rec['improvement-id-text'] || rec['improvement-id'] || rec.category || '—';
                                                    const cost = rec['indicative-cost'] || rec.cost || '—';
                                                    const saving = rec['typical-saving'] || rec.saving || '—';
                                                    return (
                                                    <tr key={idx}>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{measure}</td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{category}</td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{cost}</td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{saving}</td>
                                                    </tr>
                                                    );
                                                })}
                                            </tbody>
                                        </table>
                                    </div>
                                    {lead.epc_recommendations_fetched_at && (
                                        <p className="text-sm text-gray-500 mt-2">Last fetched: {formatDateTime(lead.epc_recommendations_fetched_at)}</p>
                                    )}
                                </div>
                            )}

                            {/* Energy Costs */}
                            {(lead.epc_data.heating_cost_current || lead.epc_data.lighting_cost_current || lead.epc_data.hot_water_cost_current) && (
                                <div>
                                    <h3 className="text-lg font-bold text-gray-900 mb-4">Estimated Energy Costs</h3>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="bg-blue-50 p-4 rounded-lg">
                                            <h4 className="font-semibold text-blue-900 mb-2">Current Costs (per year)</h4>
                                            <ul className="space-y-1 text-sm">
                                                <li>Heating: £{lead.epc_data.heating_cost_current || 0}</li>
                                                <li>Hot Water: £{lead.epc_data.hot_water_cost_current || 0}</li>
                                                <li>Lighting: £{lead.epc_data.lighting_cost_current || 0}</li>
                                            </ul>
                                        </div>
                                        <div className="bg-green-50 p-4 rounded-lg">
                                            <h4 className="font-semibold text-green-900 mb-2">Potential Costs (with improvements)</h4>
                                            <ul className="space-y-1 text-sm">
                                                <li>Heating: £{lead.epc_data.heating_cost_potential || 0}</li>
                                                <li>Hot Water: £{lead.epc_data.hot_water_cost_potential || 0}</li>
                                                <li>Lighting: £{lead.epc_data.lighting_cost_potential || 0}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Additional Details */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 className="text-lg font-bold text-gray-900 mb-3">Certificate Information</h3>
                                    <dl className="space-y-2 text-sm">
                                        <div className="grid grid-cols-3 gap-2">
                                            <dt className="font-semibold text-gray-700">Certificate No:</dt>
                                            <dd className="col-span-2 text-gray-900">{lead.epc_data.certificate_number || 'N/A'}</dd>
                                        </div>
                                        <div className="grid grid-cols-3 gap-2">
                                            <dt className="font-semibold text-gray-700">Lodgement Date:</dt>
                                            <dd className="col-span-2 text-gray-900">{lead.epc_data.lodgement_date || 'N/A'}</dd>
                                        </div>
                                        <div className="grid grid-cols-3 gap-2">
                                            <dt className="font-semibold text-gray-700">Inspection Date:</dt>
                                            <dd className="col-span-2 text-gray-900">{lead.epc_data.inspection_date || 'N/A'}</dd>
                                        </div>
                                        <div className="grid grid-cols-3 gap-2">
                                            <dt className="font-semibold text-gray-700">Tenure:</dt>
                                            <dd className="col-span-2 text-gray-900">{lead.epc_data.tenure || 'N/A'}</dd>
                                        </div>
                                    </dl>
                                </div>
                                <div>
                                    <h3 className="text-lg font-bold text-gray-900 mb-3">Environmental Impact</h3>
                                    <dl className="space-y-2 text-sm">
                                        <div className="grid grid-cols-3 gap-2">
                                            <dt className="font-semibold text-gray-700">Current CO2:</dt>
                                            <dd className="col-span-2 text-gray-900">{lead.epc_data.co2_emissions_current || 0} kg/year</dd>
                                        </div>
                                        <div className="grid grid-cols-3 gap-2">
                                            <dt className="font-semibold text-gray-700">Potential CO2:</dt>
                                            <dd className="col-span-2 text-gray-900">{lead.epc_data.co2_emissions_potential || 0} kg/year</dd>
                                        </div>
                                        <div className="grid grid-cols-3 gap-2">
                                            <dt className="font-semibold text-gray-700">Current Impact:</dt>
                                            <dd className="col-span-2 text-gray-900">{lead.epc_data.environment_impact_current || 'N/A'}</dd>
                                        </div>
                                        <div className="grid grid-cols-3 gap-2">
                                            <dt className="font-semibold text-gray-700">Potential Impact:</dt>
                                            <dd className="col-span-2 text-gray-900">{lead.epc_data.environment_impact_potential || 'N/A'}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div className="text-center py-12">
                            <div className="text-gray-400 mb-4">
                                <svg className="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 className="text-lg font-semibold text-gray-900 mb-2">No EPC Data Available</h3>
                            <p className="text-gray-600 mb-4">
                                Click the "Fetch EPC Report" button above to retrieve the Energy Performance Certificate for this property.
                            </p>
                            <p className="text-sm text-gray-500">
                                <strong>Address:</strong> {lead.house_number} {lead.street_name}, {lead.city}<br />
                                <strong>Postcode:</strong> {lead.zip_code || lead.postcode || 'Not available'}
                            </p>
                        </div>
                    )}
                </CardContent>
            </Card>

            {/* ECO4/GBIS Calculator */}
            <Eco4CalculatorCard lead={lead} />

            {/* Saved Calculations */}
            <Card className="mb-6">
                <CardHeader className="bg-green-600">
                    <CardTitle className="text-white">📊 Saved Calculations</CardTitle>
                </CardHeader>
                <CardContent className="p-6">
                    {lead.eco4_calculations && lead.eco4_calculations.length > 0 ? (
                        <div className="space-y-4">
                            {lead.eco4_calculations.map((calculation, index) => (
                                <div key={calculation.id} className="border border-gray-200 rounded-lg p-4">
                                    <div className="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 className="font-semibold text-gray-900">
                                                Calculation #{index + 1}
                                            </h4>
                                            <p className="text-sm text-gray-600">
                                                {calculation.scheme} - {calculation.starting_sap_band} | {calculation.floor_area_band}
                                            </p>
                                            <p className="text-xs text-gray-500">
                                                Saved: {formatDateTime(calculation.created_at)}
                                            </p>
                                        </div>
                                        <div className="flex items-center space-x-3">
                                            <div className="text-right">
                                                <div className="text-lg font-bold text-green-600">
                                                    £{parseFloat(calculation.total_eco_value || 0).toFixed(2)}
                                                </div>
                                                <div className="text-sm text-gray-600">
                                                    ABS: {parseFloat(calculation.total_abs || 0).toFixed(2)}
                                                </div>
                                            </div>
                                            <button
                                                onClick={() => handleDeleteCalculation(calculation.id)}
                                                className="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors"
                                                title="Delete calculation"
                                            >
                                                🗑️ Delete
                                            </button>
                                        </div>
                                    </div>
                                    
                                    {calculation.measures && calculation.measures.length > 0 && (
                                        <div className="mt-3">
                                            <h5 className="font-medium text-gray-800 mb-2">Measures:</h5>
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-2">
                                                {calculation.measures.map((measure, measureIndex) => (
                                                    <div key={measureIndex} className="bg-gray-50 p-2 rounded text-sm">
                                                        <div className="flex justify-between">
                                                            <span className="font-medium">{measure.measure_type}</span>
                                                            <span className="text-green-600 font-semibold">
                                                                £{parseFloat(measure.eco_value || 0).toFixed(2)}
                                                            </span>
                                                        </div>
                                                        <div className="text-xs text-gray-600">
                                                            ABS: {parseFloat(measure.abs_value || 0).toFixed(2)} | 
                                                            PPS: {parseFloat(measure.pps_points || 0).toFixed(2)}
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-8 text-gray-500">
                            <p>No calculations saved yet.</p>
                            <p className="text-sm mt-2">Use the calculator above to create and save calculations.</p>
                        </div>
                    )}
                </CardContent>
            </Card>

            {/* EPR Section */}
            <Card padding={false} className="mb-6">
                <CardHeader className="bg-indigo-600">
                    <div className="flex items-center justify-between">
                        <CardTitle className="text-white">EPR (Energy Performance Report) and Submission</CardTitle>
                        <Link href={route('leads.edit', lead.id)}>
                            <Button variant="secondary" size="sm">
                                <PencilIcon className="-ml-1 mr-2 h-4 w-4" />
                                Edit
                            </Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent className="p-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {/* Measures */}
                        <div className="col-span-2">
                            <h4 className="text-sm font-semibold text-gray-700 mb-2">Measures</h4>
                            {lead.epr_measures && lead.epr_measures.length > 0 ? (
                                <div className="flex flex-wrap gap-2">
                                    {lead.epr_measures.map((measure, index) => (
                                        <span key={index} className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                            {measure}
                                        </span>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-sm text-gray-500 italic">No measures selected</p>
                            )}
                        </div>

                        {/* Floor Area */}
                        <div>
                            <h4 className="text-sm font-semibold text-gray-700 mb-1">Floor Area (m²)</h4>
                            <p className="text-gray-900">{lead.floor_area || 'Not specified'}</p>
                        </div>

                        {/* Pre Rating (Before) - computed from score */}
                        <div>
                            <h4 className="text-sm font-semibold text-gray-700 mb-1">Pre Rating (Before)</h4>
                            <p className="text-gray-900">{(() => {
                                const label = getLabelFromScore(lead.epr_pre_rating_score);
                                return label ? label : 'Not specified';
                            })()}</p>
                            {lead.epr_pre_rating_score != null && (
                                <p className="text-xs text-gray-500 mt-1">Score: {lead.epr_pre_rating_score}</p>
                            )}
                        </div>

                        {/* Post Rating (After) - computed from score */}
                        <div>
                            <h4 className="text-sm font-semibold text-gray-700 mb-1">Post Rating (After)</h4>
                            <p className="text-gray-900">{(() => {
                                const label = getLabelFromScore(lead.epr_post_rating_score);
                                return label ? label : 'Not specified';
                            })()}</p>
                            {lead.epr_post_rating_score != null && (
                                <p className="text-xs text-gray-500 mt-1">Score: {lead.epr_post_rating_score}</p>
                            )}
                        </div>

                        {/* ABS */}
                        <div>
                            <h4 className="text-sm font-semibold text-gray-700 mb-1">ABS</h4>
                            <p className="text-gray-900">{lead.epr_abs ? `£${parseFloat(lead.epr_abs).toFixed(2)}` : 'Not specified'}</p>
                        </div>

                        {/* Amount Funded */}
                        <div>
                            <h4 className="text-sm font-semibold text-gray-700 mb-1">Amount Funded</h4>
                            <p className="text-gray-900">{lead.epr_amount_funded ? `£${parseFloat(lead.epr_amount_funded).toFixed(2)}` : 'Not specified'}</p>
                        </div>

                        {/* Expenses */}
                        <div className="col-span-2">
                            <div className="flex items-center justify-between mb-3">
                                <h4 className="text-sm font-semibold text-gray-700">Expenses</h4>
                                <Button type="button" variant="secondary" size="sm" onClick={handleOpenExpenseModal}>
                                    Manage Expenses
                                </Button>
                            </div>
                            {lead.epr_payments && lead.epr_payments.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                                                <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {lead.epr_payments.map((payment, index) => (
                                                <tr key={index} className="hover:bg-gray-50">
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {payment.type}
                                                        {(payment.type === 'Other' || payment.type === 'Remedial') && payment.other_details && (
                                                            <div className="text-xs text-gray-500 mt-1">
                                                                ({payment.other_details})
                                                            </div>
                                                        )}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {payment.type === 'Airbox' || payment.type === 'DMEV' ? payment.quantity : payment.type === 'VAT' ? '-' : '-'}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {payment.type === 'Airbox' ? `£${parseFloat(payment.rate || 12.50).toFixed(2)}` : payment.type === 'VAT' ? `${payment.percentage || 0}%` : '-'}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                                                        £{parseFloat(payment.amount || 0).toFixed(2)}
                                                    </td>
                                                </tr>
                                            ))}
                                            <tr className="bg-gray-100 font-bold">
                                                <td colSpan="3" className="px-4 py-3 text-sm text-gray-900 text-right">Total Expenses:</td>
                                                <td className="px-4 py-3 text-sm text-gray-900 text-right">
                                                    £{lead.epr_payments.reduce((sum, p) => sum + parseFloat(p.amount || 0), 0).toFixed(2)}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <p className="text-sm text-gray-500 italic">No expenses added</p>
                            )}
                        </div>

                        {/* Net Profit */}
                        {(lead.epr_amount_funded || (lead.epr_payments && lead.epr_payments.length > 0)) && (
                            <div className="col-span-2">
                                <div className="p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <div className="flex justify-between items-center">
                                        <h4 className="text-sm font-semibold text-gray-900">Net Profit:</h4>
                                        <p className="text-lg font-bold text-green-600">
                                            £{(
                                                (parseFloat(lead.epr_amount_funded) || 0) - 
                                                (lead.epr_payments ? lead.epr_payments.reduce((sum, p) => sum + parseFloat(p.amount || 0), 0) : 0)
                                            ).toFixed(2)}
                                        </p>
                                    </div>
                                    <p className="text-xs text-gray-500 mt-1">
                                        Amount Funded (£{parseFloat(lead.epr_amount_funded || 0).toFixed(2)}) - Total Expenses (£{lead.epr_payments ? lead.epr_payments.reduce((sum, p) => sum + parseFloat(p.amount || 0), 0).toFixed(2) : '0.00'})
                                    </p>
                                </div>
                            </div>
                        )}
                    </div>
                </CardContent>
            </Card>

            {/* Invoices Section */}
            <Card padding={false} className="mb-6">
                <CardHeader className="bg-purple-600">
                    <div className="flex items-center justify-between">
                        <CardTitle className="text-white">Invoices</CardTitle>
                        <Button 
                            variant="secondary" 
                            size="sm"
                            onClick={handleOpenInvoiceModal}
                        >
                            Create Invoice
                        </Button>
                    </div>
                </CardHeader>
                <CardContent className="p-6">
                    {lead.invoices && lead.invoices.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice No.</th>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order No.</th>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submission No.</th>
                                        <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {lead.invoices.map((invoice) => (
                                        <tr key={invoice.id} className="hover:bg-gray-50">
                                            <td className="px-4 py-3 text-sm text-gray-900">{invoice.invoice_no}</td>
                                            <td className="px-4 py-3 text-sm text-gray-900">{formatDate(invoice.invoice_date)}</td>
                                            <td className="px-4 py-3 text-sm text-gray-900">{invoice.order_no || 'N/A'}</td>
                                            <td className="px-4 py-3 text-sm text-gray-900">{invoice.submission_no || 'N/A'}</td>
                                            <td className="px-4 py-3 text-sm text-gray-900 text-right font-semibold">£{parseFloat(invoice.total || 0).toFixed(2)}</td>
                                            <td className="px-4 py-3 text-sm text-center">
                                                <div className="flex items-center justify-center gap-2">
                                                    <a
                                                        href={(() => {
                                                            try {
                                                                return route('invoices.download', invoice.id);
                                                            } catch (e) {
                                                                return `/invoices/${invoice.id}/download`;
                                                            }
                                                        })()}
                                                        className="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded transition-colors duration-200"
                                                        title="Download PDF"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                    >
                                                        <ArrowDownTrayIcon className="h-4 w-4 mr-1" />
                                                        Download
                                                    </a>
                                                    <form
                                                        onSubmit={(e) => {
                                                            e.preventDefault();
                                                            if (confirm('Are you sure you want to delete this invoice? This action cannot be undone.')) {
                                                                router.delete(
                                                                    (() => {
                                                                        try {
                                                                            return route('invoices.destroy', invoice.id);
                                                                        } catch (e) {
                                                                            return `/invoices/${invoice.id}`;
                                                                        }
                                                                    })(),
                                                                    {
                                                                        onSuccess: () => {
                                                                            // Page will refresh automatically
                                                                        },
                                                                    }
                                                                );
                                                            }
                                                        }}
                                                        className="inline"
                                                    >
                                                        <Button
                                                            type="submit"
                                                            variant="danger"
                                                            size="sm"
                                                            className="inline-flex items-center px-3 py-1.5"
                                                            title="Delete Invoice"
                                                        >
                                                            <TrashIcon className="h-4 w-4" />
                                                        </Button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <p className="text-sm text-gray-500 italic">No invoices created yet.</p>
                    )}
                </CardContent>
            </Card>

            {/* Activity Thread */}
            <Card padding={false} className="mb-6">
                <CardHeader className="bg-success-600">
                    <div className="flex items-center justify-between">
                        <CardTitle className="text-white">Activity Thread</CardTitle>
                        <Button 
                            variant="secondary" 
                            size="sm"
                            onClick={() => setShowActivityModal(true)}
                        >
                            <DocumentTextIcon className="-ml-1 mr-2 h-4 w-4" />
                            Add Activity
                        </Button>
                    </div>
                </CardHeader>
                <CardContent className="p-6">
                    {lead.activities && lead.activities.filter(a => a.type !== 'file_upload').length > 0 ? (
                        <>
                            <div className="space-y-6">
                                {[...lead.activities].filter(a => a.type !== 'file_upload').sort((a, b) => new Date(b.created_at) - new Date(a.created_at)).map((activity) => (
                                    <div key={activity.id} className="flex gap-4 pb-6 border-b border-gray-200 last:border-0 last:pb-0">
                                        <div className="flex-shrink-0">
                                            <div className={`w-10 h-10 rounded-full flex items-center justify-center text-white ${
                                                activity.type === 'note' ? 'bg-primary-500' :
                                                activity.type === 'status_change' ? 'bg-warning-500' :
                                                activity.type === 'file_upload' ? 'bg-info-500' : 'bg-gray-500'
                                            }`}>
                                                <DocumentTextIcon className="h-5 w-5" />
                                            </div>
                                        </div>
                                        <div className="flex-1">
                                            <div className="flex justify-between items-start mb-2">
                                                <div>
                                                    <span className="font-semibold text-gray-900">
                                                        {activity.user?.name || 'System'}
                                                    </span>
                                                    <span className="text-sm text-gray-500 ml-2">{activity.type}</span>
                                                </div>
                                                <span className="text-sm text-gray-500">{formatDateTime(activity.created_at)}</span>
                                            </div>
                                            <div className="p-3 bg-gray-50 rounded-lg">
                                                <p className="text-sm text-gray-900 mb-1">{activity.description}</p>
                                                {activity.message && (
                                                    <p className="text-sm text-gray-600">{activity.message}</p>
                                                )}

                                                {/* Attached Documents */}
                                                {activity.documents && activity.documents.length > 0 && (
                                                    <div className="mt-3 pt-3 border-t border-gray-200">
                                                        <p className="text-xs font-semibold text-gray-700 mb-2">Attached Documents:</p>
                                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                            {activity.documents.map((document) => {
                                                                const isImage = document.name && (
                                                                    document.name.toLowerCase().endsWith('.jpg') ||
                                                                    document.name.toLowerCase().endsWith('.jpeg') ||
                                                                    document.name.toLowerCase().endsWith('.png') ||
                                                                    document.name.toLowerCase().endsWith('.gif') ||
                                                                    document.name.toLowerCase().endsWith('.webp')
                                                                );

                                                                return (
                                                                    <div key={document.id} className="flex items-start gap-2 p-2 border border-gray-200 rounded-lg hover:bg-gray-50">
                                                                        {isImage && document.storage_path ? (
                                                                            <a 
                                                                                href={route('documents.view', document.id)}
                                                                                target="_blank"
                                                                                rel="noopener noreferrer"
                                                                                className="flex-shrink-0"
                                                                            >
                                                                                <img 
                                                                                    src={`/storage/${document.storage_path}`}
                                                                                    alt={document.name}
                                                                                    className="h-16 w-16 object-cover rounded border border-gray-300"
                                                                                />
                                                                            </a>
                                                                        ) : (
                                                                            <div className="flex-shrink-0 h-16 w-16 bg-gray-100 rounded flex items-center justify-center">
                                                                                <svg className="h-8 w-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                                                    <path d="M8 2a1 1 0 000 2h2a1 1 0 100-2H8z" />
                                                                                    <path d="M3 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6h-4.586l1.293-1.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L10.414 13H15v3a2 2 0 01-2 2H5a2 2 0 01-2-2V5z" />
                                                                                </svg>
                                                                            </div>
                                                                        )}
                                                                        <div className="flex-1 min-w-0">
                                                                            <div className="flex items-center gap-2 flex-wrap">
                                                                                {canViewDocument(document) && (
                                                                                    <a
                                                                                        href={route('documents.view', document.id)}
                                                                                        target="_blank"
                                                                                        rel="noopener noreferrer"
                                                                                        className="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline"
                                                                                        title="View document"
                                                                                    >
                                                                                        <EyeIcon className="h-4 w-4 inline mr-1" />
                                                                                        View
                                                                                    </a>
                                                                                )}
                                                                                <a 
                                                                                    href={route('documents.download', document.id)}
                                                                                    download
                                                                                    className="text-sm font-medium text-primary-600 hover:text-primary-800 hover:underline"
                                                                                    title="Download document"
                                                                                >
                                                                                    {document.name}
                                                                                </a>
                                                                            </div>
                                                                            <p className="text-xs text-gray-500">
                                                                                {(document.size_bytes / 1024).toFixed(2)} KB
                                                                            </p>
                                                                        </div>
                                                                        <button
                                                                            onClick={() => {
                                                                                if (confirm('Are you sure you want to delete this document?')) {
                                                                                    router.delete(route('documents.destroy', document.id), {
                                                                                        onSuccess: () => {
                                                                                            // Document will be removed from the list automatically
                                                                                        }
                                                                                    });
                                                                                }
                                                                            }}
                                                                            className="flex-shrink-0 p-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-full transition-colors duration-200 group"
                                                                            title="Delete document"
                                                                        >
                                                                            <TrashIcon className="h-4 w-4 group-hover:scale-110 transition-transform duration-200" />
                                                                        </button>
                                                                    </div>
                                                                );
                                                            })}
                                                        </div>
                                                    </div>
                                                )}
                                            </div>

                                            {/* Activity Actions */}
                                            <div className="mt-2 flex items-center gap-2 text-sm">
                                                <Link 
                                                    href={route('activities.edit', activity.id)}
                                                    className="text-primary-600 hover:text-primary-800"
                                                >
                                                    Edit
                                                </Link>
                                                <span className="text-gray-300">|</span>
                                                <button
                                                    onClick={() => {
                                                        // Will handle attach document
                                                        alert('Attach document functionality - to be implemented');
                                                    }}
                                                    className="text-primary-600 hover:text-primary-800"
                                                >
                                                    Attach Document
                                                </button>
                                                <span className="text-gray-300">|</span>
                                                <button
                                                    onClick={() => {
                                                        if (confirm('Are you sure you want to delete this activity?')) {
                                                            router.delete(route('activities.destroy', activity.id));
                                                        }
                                                    }}
                                                    className="text-danger-600 hover:text-danger-800"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {/* Quick Add Activity Form */}
                            <div className="mt-6 pt-6 border-t border-gray-200">
                                <h4 className="text-sm font-semibold text-gray-900 mb-4">Add New Activity</h4>
                                <QuickAddActivityForm leadId={lead.id} activityTypes={activityTypes} />
                            </div>
                        </>
                    ) : (
                        <div className="text-center py-8">
                            <p className="text-gray-500 mb-4">No activity history found.</p>
                            {/* Quick Add Activity Form for empty state */}
                            <div className="mt-6 pt-6 border-t border-gray-200">
                                <h4 className="text-sm font-semibold text-gray-900 mb-4">Add New Activity</h4>
                                <QuickAddActivityForm leadId={lead.id} activityTypes={activityTypes} />
                            </div>
                        </div>
                    )}
                </CardContent>
            </Card>

            {/* Documents */}
            <Card padding={false}>
                <CardHeader className="bg-gray-600">
                    <div className="flex items-center justify-between">
                        <CardTitle className="text-white">Documents</CardTitle>
                        <div className="flex items-center space-x-2">
                            {lead.documents && lead.documents.length > 0 && (
                                <Button 
                                    variant={bulkDeleteMode ? "danger" : "secondary"}
                                    size="sm"
                                    onClick={toggleBulkDeleteMode}
                                >
                                    {bulkDeleteMode ? 'Cancel' : 'Bulk Delete'}
                                </Button>
                            )}
                            <Button 
                                variant="secondary" 
                                size="sm"
                                onClick={() => setShowDocumentModal(true)}
                            >
                                Add Document
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent className="p-6">
                    {lead.documents && lead.documents.length > 0 ? (
                        <div className="space-y-3">
                            {Object.entries(groupDocumentsByKind()).map(([kind, documents]) => (
                                <div key={kind} className="border border-gray-200 rounded-lg overflow-hidden">
                                    {/* Folder Header */}
                                    <div 
                                        className="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 cursor-pointer transition-colors"
                                        onClick={() => toggleFolder(kind)}
                                    >
                                        <div className="flex items-center space-x-3">
                                            {expandedFolders[kind] ? (
                                                <>
                                                    <FolderOpenIcon className="h-6 w-6 text-yellow-500" />
                                                    <ChevronDownIcon className="h-4 w-4 text-gray-500" />
                                                </>
                                            ) : (
                                                <>
                                                    <FolderIcon className="h-6 w-6 text-yellow-500" />
                                                    <ChevronRightIcon className="h-4 w-4 text-gray-500" />
                                                </>
                                            )}
                                            <div>
                                                <h3 className="text-sm font-semibold text-gray-900">
                                                    {formatDocumentKind(kind)}
                                                </h3>
                                                <p className="text-xs text-gray-500">
                                                    {documents.length} {documents.length === 1 ? 'file' : 'files'}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="text-xs text-gray-500">
                                            {expandedFolders[kind] ? 'Click to collapse' : 'Click to expand'}
                                        </div>
                                    </div>

                                    {/* Folder Contents */}
                                    {expandedFolders[kind] && (
                                        <div className="bg-white">
                                            <table className="min-w-full divide-y divide-gray-200">
                                                <thead className="bg-gray-50">
                                                    <tr>
                                                        {bulkDeleteMode && (
                                                            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                                <input
                                                                    type="checkbox"
                                                                    checked={selectedDocuments.length === documents.length && documents.length > 0}
                                                                    onChange={() => handleSelectAllDocuments(documents)}
                                                                    className="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                                                />
                                                            </th>
                                                        )}
                                                        <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                                        <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                                                        <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Uploaded</th>
                                                        <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody className="bg-white divide-y divide-gray-200">
                                                    {documents.map((document) => (
                                                        <tr key={document.id} className={`hover:bg-gray-50 ${selectedDocuments.includes(document.id) ? 'bg-blue-50' : ''}`}>
                                                            {bulkDeleteMode && (
                                                                <td className="px-4 py-3 text-sm">
                                                                    <input
                                                                        type="checkbox"
                                                                        checked={selectedDocuments.includes(document.id)}
                                                                        onChange={() => handleSelectDocument(document.id)}
                                                                        className="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                                                    />
                                                                </td>
                                                            )}
                                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                                <div className="flex items-center space-x-2">
                                                                    <DocumentTextIcon className="h-4 w-4 text-gray-400" />
                                                                    <span>{document.name}</span>
                                                                </div>
                                                            </td>
                                                            <td className="px-4 py-3 text-sm text-gray-500">
                                                                {(document.size_bytes / 1024).toFixed(2)} KB
                                                            </td>
                                                            <td className="px-4 py-3 text-sm text-gray-500">
                                                                {formatDate(document.created_at)}
                                                            </td>
                                                            <td className="px-4 py-3 text-sm">
                                                                <div className="flex items-center space-x-2">
                                                                    {canViewDocument(document) && (
                                                                        <a
                                                                            href={route('documents.view', document.id)}
                                                                            target="_blank"
                                                                            rel="noopener noreferrer"
                                                                            className="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded transition-colors duration-200"
                                                                            title="View document"
                                                                        >
                                                                            <EyeIcon className="h-4 w-4 mr-1" />
                                                                            View
                                                                        </a>
                                                                    )}
                                                                    <a
                                                                        href={route('documents.download', document.id)}
                                                                        download
                                                                        className="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded transition-colors duration-200"
                                                                        title="Download document"
                                                                    >
                                                                        Download
                                                                    </a>
                                                                    {!bulkDeleteMode && (
                                                                        <button
                                                                            onClick={() => {
                                                                                if (confirm('Are you sure you want to delete this document?')) {
                                                                                    router.delete(route('documents.destroy', document.id), {
                                                                                        onSuccess: () => {
                                                                                            // Document will be removed from the list automatically
                                                                                        }
                                                                                    });
                                                                                }
                                                                            }}
                                                                            className="p-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-full transition-colors duration-200 group"
                                                                            title="Delete document"
                                                                        >
                                                                            <TrashIcon className="h-4 w-4 group-hover:scale-110 transition-transform duration-200" />
                                                                        </button>
                                                                    )}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    )}
                                </div>
                            ))}
                            
                            {/* Bulk Delete Controls */}
                            {bulkDeleteMode && (
                                <div className="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center space-x-2">
                                            <span className="text-sm text-red-800">
                                                {selectedDocuments.length} document{selectedDocuments.length !== 1 ? 's' : ''} selected
                                            </span>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Button
                                                variant="secondary"
                                                size="sm"
                                                onClick={() => setSelectedDocuments([])}
                                                disabled={selectedDocuments.length === 0}
                                            >
                                                Clear Selection
                                            </Button>
                                            <Button
                                                variant="danger"
                                                size="sm"
                                                onClick={handleBulkDelete}
                                                disabled={selectedDocuments.length === 0}
                                            >
                                                Delete Selected ({selectedDocuments.length})
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    ) : (
                        <div className="text-center py-8">
                            <FolderIcon className="mx-auto h-12 w-12 text-gray-400" />
                            <p className="mt-2 text-sm text-gray-500">No documents found.</p>
                            <p className="text-xs text-gray-400">Upload documents to organize them by type</p>
                        </div>
                    )}
                </CardContent>
            </Card>

            {/* Add Activity Modal */}
            <AddActivityModal
                show={showActivityModal}
                onClose={() => setShowActivityModal(false)}
                leadId={lead.id}
                activityTypes={activityTypes}
                documentKinds={documentKinds}
            />

            {/* Add Document Modal */}
            <AddDocumentModal
                show={showDocumentModal}
                onClose={() => setShowDocumentModal(false)}
                leadId={lead.id}
                documentKinds={documentKinds}
            />

            {/* Expense Modal */}
            <Modal show={showExpenseModal} onClose={handleCloseExpenseModal} maxWidth="4xl">
                <ModalHeader>Manage Expenses</ModalHeader>
                <ModalBody>
                    <div className="space-y-4">
                        {eprPayments.length > 0 ? (
                            <>
                                {eprPayments.map((payment, index) => {
                                    const safePayment = {
                                        type: payment?.type || '',
                                        amount: payment?.amount || '',
                                        quantity: payment?.quantity || '',
                                        rate: payment?.rate || '',
                                        percentage: payment?.percentage || '',
                                        other_details: payment?.other_details || ''
                                    };
                                    return (
                                        <div key={index} className="p-4 border border-gray-200 rounded-lg bg-gray-50">
                                            <div className="grid grid-cols-12 gap-3">
                                                <div className="col-span-11">
                                                    <div className="grid grid-cols-1 md:grid-cols-4 gap-3">
                                                        <FormSelect
                                                            label="Payment Type"
                                                            value={safePayment.type}
                                                            onChange={(e) => updateEprPayment(index, 'type', e.target.value)}
                                                        >
                                                            <option value="">Select Type</option>
                                                            <option value="Data Match">Data Match</option>
                                                            <option value="Land Registry">Land Registry</option>
                                                            <option value="Boiler Material">Boiler Material</option>
                                                            <option value="Gas Engineer">Gas Engineer</option>
                                                            <option value="Loft Material">Loft Material</option>
                                                            <option value="Loft Labour">Loft Labour</option>
                                                            <option value="Airbox">Airbox</option>
                                                            <option value="DMEV">DMEV</option>
                                                            <option value="Administrative Charges">Administrative Charges</option>
                                                            <option value="Coordination">Coordination</option>
                                                            <option value="Commission">Commission</option>
                                                            <option value="Remedial">Remedial</option>
                                                            <option value="VAT">VAT</option>
                                                            <option value="GDGC">GDGC</option>
                                                            <option value="IBG">IBG</option>
                                                            <option value="Surveyor">Surveyor</option>
                                                            <option value="Other">Other</option>
                                                        </FormSelect>

                                                        {safePayment.type === 'VAT' ? (
                                                            <>
                                                                <FormInput
                                                                    label="Percentage (%)"
                                                                    type="number"
                                                                    step="0.01"
                                                                    value={safePayment.percentage}
                                                                    onChange={(e) => updateEprPayment(index, 'percentage', e.target.value)}
                                                                    placeholder="20"
                                                                />
                                                                <div className="col-span-2">
                                                                    <FormInput
                                                                        label="VAT Amount"
                                                                        type="number"
                                                                        value={safePayment.amount}
                                                                        disabled
                                                                        className="bg-gray-100"
                                                                    />
                                                                </div>
                                                            </>
                                                        ) : safePayment.type === 'Airbox' ? (
                                                            <>
                                                                <FormInput
                                                                    label="Quantity"
                                                                    type="number"
                                                                    value={safePayment.quantity}
                                                                    onChange={(e) => updateEprPayment(index, 'quantity', e.target.value)}
                                                                    placeholder="0"
                                                                />
                                                                <FormInput
                                                                    label="Rate"
                                                                    type="number"
                                                                    step="0.01"
                                                                    value={safePayment.rate || '12.50'}
                                                                    disabled
                                                                    className="bg-gray-100"
                                                                />
                                                                <FormInput
                                                                    label="Amount"
                                                                    type="number"
                                                                    value={safePayment.amount}
                                                                    disabled
                                                                    className="bg-gray-100"
                                                                />
                                                            </>
                                                        ) : safePayment.type === 'DMEV' ? (
                                                            <>
                                                                <FormInput
                                                                    label="Quantity"
                                                                    type="number"
                                                                    value={safePayment.quantity}
                                                                    onChange={(e) => updateEprPayment(index, 'quantity', e.target.value)}
                                                                    placeholder="0"
                                                                />
                                                                <div className="col-span-2">
                                                                    <FormInput
                                                                        label="Amount"
                                                                        type="number"
                                                                        step="0.01"
                                                                        value={safePayment.amount}
                                                                        onChange={(e) => updateEprPayment(index, 'amount', e.target.value)}
                                                                        placeholder="0.00"
                                                                    />
                                                                </div>
                                                            </>
                                                        ) : safePayment.type === 'Remedial' ? (
                                                            <>
                                                                <div className="col-span-2">
                                                                    <FormInput
                                                                        label="Description"
                                                                        type="text"
                                                                        value={safePayment.other_details}
                                                                        onChange={(e) => updateEprPayment(index, 'other_details', e.target.value)}
                                                                        placeholder="Enter description"
                                                                    />
                                                                </div>
                                                                <div className="col-span-1">
                                                                    <FormInput
                                                                        label="Amount"
                                                                        type="number"
                                                                        step="0.01"
                                                                        value={safePayment.amount}
                                                                        onChange={(e) => updateEprPayment(index, 'amount', e.target.value)}
                                                                        placeholder="0.00"
                                                                    />
                                                                </div>
                                                            </>
                                                        ) : safePayment.type === 'Other' ? (
                                                            <>
                                                                <div className="col-span-2">
                                                                    <FormInput
                                                                        label="Description"
                                                                        type="text"
                                                                        value={safePayment.other_details}
                                                                        onChange={(e) => updateEprPayment(index, 'other_details', e.target.value)}
                                                                        placeholder="Enter description"
                                                                    />
                                                                </div>
                                                                <div className="col-span-1">
                                                                    <FormInput
                                                                        label="Amount"
                                                                        type="number"
                                                                        step="0.01"
                                                                        value={safePayment.amount}
                                                                        onChange={(e) => updateEprPayment(index, 'amount', e.target.value)}
                                                                        placeholder="0.00"
                                                                    />
                                                                </div>
                                                            </>
                                                        ) : (
                                                            <div className="col-span-3">
                                                                <FormInput
                                                                    label="Amount"
                                                                    type="number"
                                                                    step="0.01"
                                                                    value={safePayment.amount}
                                                                    onChange={(e) => updateEprPayment(index, 'amount', e.target.value)}
                                                                    placeholder="0.00"
                                                                />
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                                <div className="col-span-1 flex items-end">
                                                    <Button
                                                        type="button"
                                                        variant="danger"
                                                        size="sm"
                                                        onClick={() => removeEprPayment(index)}
                                                    >
                                                        ×
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                                <div className="pt-2 border-t border-gray-200">
                                    <div className="flex justify-between items-center">
                                        <div>
                                            <p className="text-sm font-semibold text-gray-700">Total Expenses:</p>
                                            <p className="text-lg font-bold text-gray-900">
                                                £{eprPayments.reduce((sum, p) => sum + parseFloat(p.amount || 0), 0).toFixed(2)}
                                            </p>
                                        </div>
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            size="sm"
                                            onClick={addEprPayment}
                                        >
                                            Add Another Expense
                                        </Button>
                                    </div>
                                </div>
                            </>
                        ) : (
                            <div className="text-center py-8">
                                <p className="text-sm text-gray-500 italic mb-4">No expenses added yet.</p>
                                <Button
                                    type="button"
                                    variant="primary"
                                    size="sm"
                                    onClick={addEprPayment}
                                >
                                    Add First Expense
                                </Button>
                            </div>
                        )}
                    </div>
                </ModalBody>
                <ModalFooter>
                    <Button
                        type="button"
                        variant="secondary"
                        onClick={handleCloseExpenseModal}
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        variant="primary"
                        onClick={saveExpenses}
                        disabled={isSavingExpenses}
                    >
                        {isSavingExpenses ? 'Saving...' : 'Save Expenses'}
                    </Button>
                </ModalFooter>
            </Modal>

            {/* Invoice Modal */}
            <Modal show={showInvoiceModal} onClose={handleCloseInvoiceModal} maxWidth="4xl">
                <ModalHeader>Create Invoice</ModalHeader>
                <ModalBody>
                    <div className="space-y-6">
                        {/* Buyer Information */}
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">Bill To</h3>
                            <div className="grid grid-cols-1 gap-4">
                                <FormInput
                                    label="Company Name"
                                    type="text"
                                    value={invoiceData.buyer_name}
                                    onChange={(e) => updateInvoiceField('buyer_name', e.target.value)}
                                    placeholder="e.g., Anesco Energy Limited"
                                    required
                                />
                                <FormTextarea
                                    label="Address"
                                    value={invoiceData.buyer_address}
                                    onChange={(e) => updateInvoiceField('buyer_address', e.target.value)}
                                    placeholder="Unit 8-9 Easter Park, Benyon Road, Silchester, Reading, Berkshire, RG7 2PQ"
                                    rows={3}
                                    required
                                />
                            </div>
                        </div>

                        {/* Invoice Details */}
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">Invoice Details</h3>
                            <div className="grid grid-cols-2 gap-4">
                                <FormInput
                                    label="Invoice Date"
                                    type="date"
                                    value={invoiceData.invoice_date}
                                    onChange={(e) => updateInvoiceField('invoice_date', e.target.value)}
                                    required
                                />
                            <FormInput
                                label="Invoice No."
                                type="text"
                                value={invoiceData.invoice_no}
                                onChange={(e) => updateInvoiceField('invoice_no', e.target.value)}
                                placeholder="e.g., 0020"
                                required
                            />
                            <FormInput
                                label="Order No."
                                type="text"
                                value={invoiceData.order_no}
                                onChange={(e) => updateInvoiceField('order_no', e.target.value)}
                                placeholder="e.g., 30005218"
                            />
                            <FormInput
                                label="Submission No."
                                type="text"
                                value={invoiceData.submission_no}
                                onChange={(e) => updateInvoiceField('submission_no', e.target.value)}
                                placeholder="e.g., 7127"
                            />
                            <FormInput
                                label="PO No."
                                type="text"
                                value={invoiceData.po_no}
                                onChange={(e) => updateInvoiceField('po_no', e.target.value)}
                                placeholder="e.g., 1"
                            />
                            <FormInput
                                label="Due Date"
                                type="date"
                                value={invoiceData.due_date}
                                onChange={(e) => updateInvoiceField('due_date', e.target.value)}
                            />
                            </div>
                        </div>

                        {/* Line Items */}
                        <div>
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-lg font-semibold text-gray-900">Line Items</h3>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    size="sm"
                                    onClick={addInvoiceLineItem}
                                >
                                    Add Line Item
                                </Button>
                            </div>
                            
                            <div className="space-y-4">
                                {invoiceData.line_items.map((item, index) => (
                                    <div key={index} className="p-4 border border-gray-200 rounded-lg bg-gray-50">
                                        <div className="grid grid-cols-12 gap-3">
                                            <div className="col-span-5">
                                                <FormInput
                                                    label="Details"
                                                    type="text"
                                                    value={item.details}
                                                    onChange={(e) => updateInvoiceLineItem(index, 'details', e.target.value)}
                                                    placeholder="Item description"
                                                    required
                                                />
                                            </div>
                                            <div className="col-span-2">
                                                <FormInput
                                                    label="Qty"
                                                    type="number"
                                                    step="0.001"
                                                    value={item.qty}
                                                    onChange={(e) => updateInvoiceLineItem(index, 'qty', e.target.value)}
                                                    placeholder="0"
                                                    required
                                                />
                                            </div>
                                            <div className="col-span-2">
                                                <FormInput
                                                    label="Price"
                                                    type="number"
                                                    step="0.01"
                                                    value={item.price}
                                                    onChange={(e) => updateInvoiceLineItem(index, 'price', e.target.value)}
                                                    placeholder="0.00"
                                                    required
                                                    // Allow negative values for credits/adjustments
                                                />
                                            </div>
                                            <div className="col-span-2">
                                                <FormInput
                                                    label="Total"
                                                    type="text"
                                                    value={item.total || '0.00'}
                                                    readOnly
                                                    className="bg-gray-100"
                                                />
                                            </div>
                                            <div className="col-span-1 flex items-end">
                                                <Button
                                                    type="button"
                                                    variant="danger"
                                                    size="sm"
                                                    onClick={() => removeInvoiceLineItem(index)}
                                                >
                                                    ×
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {/* Summary */}
                            <div className="mt-6 pt-4 border-t border-gray-200">
                                <div className="flex justify-end">
                                    <div className="w-64 space-y-2">
                                        <div className="flex justify-between text-sm">
                                            <span className="text-gray-700">Subtotal:</span>
                                            <span className="font-semibold">
                                                £{invoiceData.line_items.reduce((sum, item) => {
                                                    const qty = parseFloat(item.qty || 0);
                                                    const price = parseFloat(item.price || 0);
                                                    return sum + (qty * price);
                                                }, 0).toFixed(2)}
                                            </span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-gray-700">VAT @ 20%:</span>
                                            <span className="font-semibold">
                                                £{(invoiceData.line_items.reduce((sum, item) => {
                                                    const qty = parseFloat(item.qty || 0);
                                                    const price = parseFloat(item.price || 0);
                                                    return sum + (qty * price);
                                                }, 0) * 0.20).toFixed(2)}
                                            </span>
                                        </div>
                                        <div className="flex justify-between text-lg font-bold pt-2 border-t border-gray-300">
                                            <span>Invoice Total:</span>
                                            <span>
                                                £{(invoiceData.line_items.reduce((sum, item) => {
                                                    const qty = parseFloat(item.qty || 0);
                                                    const price = parseFloat(item.price || 0);
                                                    return sum + (qty * price);
                                                }, 0) * 1.20).toFixed(2)}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </ModalBody>
                <ModalFooter>
                    <Button
                        type="button"
                        variant="secondary"
                        onClick={handleCloseInvoiceModal}
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        variant="primary"
                        onClick={saveInvoice}
                        disabled={isSavingInvoice}
                    >
                        {isSavingInvoice ? 'Saving...' : 'Create Invoice'}
                    </Button>
                </ModalFooter>
            </Modal>

            {/* EPC Certificate Selection Modal */}
            {showEpcSelectionModal && (
                <div className="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div className="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        {/* Background overlay */}
                        <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onClick={() => {
                            setShowEpcSelectionModal(false);
                            setEpcSearchTerm('');
                        }}></div>

                        {/* Modal panel */}
                        <div className="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                            <div className="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div className="sm:flex sm:items-start">
                                    <div className="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                        <h3 className="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                            🏠 Select EPC Certificate
                                        </h3>
                                        <p className="mt-2 text-sm text-gray-500">
                                            {epcCertificates.length} EPC certificates found for this postcode. Please select the correct property.
                                        </p>
                                        
                                        {/* Search Input */}
                                        <div className="mt-4 relative">
                                            <input
                                                type="text"
                                                placeholder="Search by address, property type, or date..."
                                                value={epcSearchTerm}
                                                onChange={(e) => setEpcSearchTerm(e.target.value)}
                                                className="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                            />
                                            {epcSearchTerm && (
                                                <button
                                                    onClick={() => setEpcSearchTerm('')}
                                                    className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                                    title="Clear search"
                                                >
                                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            )}
                                        </div>
                                        
                                        {/* Update Address Toggle */}
                                        <div className="mt-3">
                                            <label className="inline-flex items-center text-sm text-gray-700">
                                                <input type="checkbox" id="updateAddressChk" className="mr-2 rounded border-gray-300" />
                                                Update Property Address on selection
                                            </label>
                                        </div>

                                        {/* Filtered Results */}
                                        {(() => {
                                            const filteredCertificates = epcCertificates.filter(cert => {
                                                if (!epcSearchTerm) return true;
                                                const searchLower = epcSearchTerm.toLowerCase();
                                                return (
                                                    (cert.address || cert['address1'] || '').toLowerCase().includes(searchLower) ||
                                                    (cert['address2'] || '').toLowerCase().includes(searchLower) ||
                                                    (cert['address3'] || '').toLowerCase().includes(searchLower) ||
                                                    (cert.postcode || '').toLowerCase().includes(searchLower) ||
                                                    (cert['property-type'] || '').toLowerCase().includes(searchLower) ||
                                                    (cert['lodgement-date'] || '').includes(searchLower) ||
                                                    (cert['current-energy-rating'] || '').toLowerCase().includes(searchLower)
                                                );
                                            });
                                            
                                            return (
                                                <>
                                                    {epcSearchTerm && (
                                                        <p className="mt-2 text-sm text-gray-600">
                                                            Showing {filteredCertificates.length} of {epcCertificates.length} certificates
                                                        </p>
                                                    )}
                                                    
                                                    <div className="mt-4 space-y-3 max-h-[600px] overflow-y-auto">
                                                        {filteredCertificates.length > 0 ? (
                                                            filteredCertificates.map((cert, index) => (
                                                <div
                                                    key={index}
                                                    className="border-2 border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:bg-blue-50 transition-colors cursor-pointer"
                                                    onClick={() => {
                                                        const chk = document.getElementById('updateAddressChk');
                                                        const updateAddress = chk && chk.checked;
                                                        const payload = { ...cert };
                                                        if (updateAddress) {
                                                            payload.__update_address = true;
                                                        }
                                                        handleSelectEpcCertificate(payload);
                                                    }}
                                                >
                                                    <div className="flex items-start justify-between">
                                                        <div className="flex-1">
                                                            <h4 className="text-base font-semibold text-gray-900">
                                                                {cert.address || cert['address1'] || 'Address not available'}
                                                            </h4>
                                                            {cert['address2'] && (
                                                                <p className="text-sm text-gray-600">{cert['address2']}</p>
                                                            )}
                                                            {cert['address3'] && (
                                                                <p className="text-sm text-gray-600">{cert['address3']}</p>
                                                            )}
                                                            <p className="text-sm text-gray-600 font-medium">{cert.postcode}</p>
                                                        </div>
                                                        
                                                        <div className="ml-4 flex flex-col items-end">
                                                            <div className={`inline-flex items-center justify-center w-16 h-16 rounded-full text-3xl font-bold ${
                                                                cert['current-energy-rating'] === 'A' || cert['current-energy-rating'] === 'B' ? 'bg-green-500 text-white' :
                                                                cert['current-energy-rating'] === 'C' || cert['current-energy-rating'] === 'D' ? 'bg-yellow-500 text-white' :
                                                                'bg-red-500 text-white'
                                                            }`}>
                                                                {cert['current-energy-rating'] || 'N/A'}
                                                            </div>
                                                            <p className="text-sm text-gray-600 mt-1">Score: {cert['current-energy-efficiency'] || 'N/A'}</p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div className="mt-3 grid grid-cols-2 gap-2 text-sm">
                                                        <div>
                                                            <span className="text-gray-500">Property Type:</span>
                                                            <span className="ml-2 font-medium">{cert['property-type'] || 'N/A'}</span>
                                                        </div>
                                                        <div>
                                                            <span className="text-gray-500">Floor Area:</span>
                                                            <span className="ml-2 font-medium">{cert['total-floor-area'] || 'N/A'} m²</span>
                                                        </div>
                                                        <div>
                                                            <span className="text-gray-500">Construction:</span>
                                                            <span className="ml-2 font-medium">{cert['construction-age-band'] || 'N/A'}</span>
                                                        </div>
                                                        <div>
                                                            <span className="text-gray-500">Lodgement Date:</span>
                                                            <span className="ml-2 font-medium">{cert['lodgement-date'] || 'N/A'}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            ))
                                                        ) : (
                                                            <div className="text-center py-8">
                                                                <p className="text-gray-500">No certificates match your search.</p>
                                                                <button
                                                                    onClick={() => setEpcSearchTerm('')}
                                                                    className="mt-2 text-blue-600 hover:text-blue-800 text-sm font-medium"
                                                                >
                                                                    Clear search
                                                                </button>
                                                            </div>
                                                        )}
                                                    </div>
                                                </>
                                            );
                                        })()}
                                    </div>
                                </div>
                            </div>
                            <div className="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <Button
                                    variant="secondary"
                                    onClick={() => {
                                        setShowEpcSelectionModal(false);
                                        setEpcSearchTerm('');
                                    }}
                                >
                                    Cancel
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
