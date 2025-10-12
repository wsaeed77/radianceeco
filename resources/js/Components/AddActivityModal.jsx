import { useForm } from '@inertiajs/react';
import Modal, { ModalHeader, ModalBody, ModalFooter } from './Modal';
import FormSelect from './FormSelect';
import FormInput from './FormInput';
import FormTextarea from './FormTextarea';
import Button from './Button';

export default function AddActivityModal({ show, onClose, leadId, activityTypes, documentKinds }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        lead_id: leadId,
        type: '',
        description: '',
        message: '',
        document: null,
        document_kind: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('activities.store'), {
            onSuccess: () => {
                reset();
                onClose();
            },
        });
    };

    const handleClose = () => {
        reset();
        onClose();
    };

    const handleFileChange = (e) => {
        if (e.target.files && e.target.files[0]) {
            setData('document', e.target.files[0]);
        }
    };

    return (
        <Modal show={show} onClose={handleClose} maxWidth="lg">
            <form onSubmit={handleSubmit}>
                <ModalHeader>Add Activity</ModalHeader>
                
                <ModalBody>
                    <div className="space-y-4">
                        <FormSelect
                            label="Activity Type"
                            value={data.type}
                            onChange={(e) => setData('type', e.target.value)}
                            error={errors.type}
                            required
                        >
                            <option value="">Select Activity Type</option>
                            {activityTypes?.map((type) => (
                                <option key={type.value} value={type.value}>
                                    {type.name}
                                </option>
                            ))}
                        </FormSelect>

                        <FormInput
                            label="Description"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            error={errors.description}
                            placeholder="Brief description of the activity"
                            required
                        />

                        <FormTextarea
                            label="Details"
                            value={data.message}
                            onChange={(e) => setData('message', e.target.value)}
                            error={errors.message}
                            placeholder="Additional details (optional)"
                            rows={4}
                        />

                        {/* Document Upload Section */}
                        <div className="border-t pt-4 mt-4">
                            <h4 className="text-sm font-semibold text-gray-700 mb-3">Attach Document (Optional)</h4>
                            
                            {data.document && (
                                <FormSelect
                                    label="Document Type"
                                    value={data.document_kind}
                                    onChange={(e) => setData('document_kind', e.target.value)}
                                    error={errors.document_kind}
                                    required={!!data.document}
                                >
                                    <option value="">Select Document Type</option>
                                    {documentKinds?.map((kind) => (
                                        <option key={kind.value} value={kind.value}>
                                            {kind.label}
                                        </option>
                                    ))}
                                </FormSelect>
                            )}

                            <div className="mt-3">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Upload Document
                                </label>
                                <input
                                    type="file"
                                    onChange={handleFileChange}
                                    className="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                />
                                {errors.document && (
                                    <p className="mt-1 text-sm text-danger-600">{errors.document}</p>
                                )}
                                <p className="mt-1 text-sm text-gray-500">
                                    Maximum file size: 10MB
                                </p>
                            </div>

                            {data.document && (
                                <div className="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <p className="text-sm text-blue-800">
                                        <strong>Selected:</strong> {data.document.name}
                                    </p>
                                    <p className="text-xs text-blue-600 mt-1">
                                        Size: {(data.document.size / 1024).toFixed(2)} KB
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>
                </ModalBody>

                <ModalFooter>
                    <Button
                        type="button"
                        variant="secondary"
                        onClick={handleClose}
                        disabled={processing}
                    >
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        variant="success"
                        disabled={processing}
                    >
                        {processing ? 'Saving...' : 'Save Activity'}
                    </Button>
                </ModalFooter>
            </form>
        </Modal>
    );
}

