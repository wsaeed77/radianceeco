import { useForm } from '@inertiajs/react';
import Modal, { ModalHeader, ModalBody, ModalFooter } from './Modal';
import FormSelect from './FormSelect';
import Button from './Button';

export default function AddDocumentModal({ show, onClose, leadId, documentKinds, activityId = null }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        lead_id: leadId,
        activity_id: activityId,
        kind: '',
        document: null,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('documents.store'), {
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
                <ModalHeader>Add Document</ModalHeader>
                
                <ModalBody>
                    <div className="space-y-4">
                        <FormSelect
                            label="Document Type"
                            value={data.kind}
                            onChange={(e) => setData('kind', e.target.value)}
                            error={errors.kind}
                            required
                        >
                            <option value="">Select Document Type</option>
                            {documentKinds?.map((kind) => (
                                <option key={kind.value} value={kind.value}>
                                    {kind.label}
                                </option>
                            ))}
                        </FormSelect>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Upload Document <span className="text-danger-600">*</span>
                            </label>
                            <input
                                type="file"
                                onChange={handleFileChange}
                                className="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                required
                            />
                            {errors.document && (
                                <p className="mt-1 text-sm text-danger-600">{errors.document}</p>
                            )}
                            <p className="mt-1 text-sm text-gray-500">
                                Maximum file size: 10MB
                            </p>
                        </div>

                        {data.document && (
                            <div className="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p className="text-sm text-blue-800">
                                    <strong>Selected:</strong> {data.document.name}
                                </p>
                                <p className="text-xs text-blue-600 mt-1">
                                    Size: {(data.document.size / 1024).toFixed(2)} KB
                                </p>
                            </div>
                        )}
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
                        variant="primary"
                        disabled={processing}
                    >
                        {processing ? 'Uploading...' : 'Upload Document'}
                    </Button>
                </ModalFooter>
            </form>
        </Modal>
    );
}

