import { useForm } from '@inertiajs/react';
import Modal, { ModalHeader, ModalBody, ModalFooter } from './Modal';
import FormSelect from './FormSelect';
import Button from './Button';

export default function AddDocumentModal({ show, onClose, leadId, documentKinds, activityId = null }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        lead_id: leadId,
        activity_id: activityId,
        kind: '',
        documents: [],
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
        if (e.target.files && e.target.files.length > 0) {
            let files = Array.from(e.target.files);
            
            // Validate file sizes (10MB max per file) and count (max 10 files)
            const maxSize = 10 * 1024 * 1024; // 10MB in bytes
            const maxFiles = 10;
            const validFiles = [];
            const invalidFiles = [];
            
            if (files.length > maxFiles) {
                alert(`You can only upload up to ${maxFiles} files at once. Only the first ${maxFiles} files will be selected.`);
                files = files.slice(0, maxFiles);
            }
            
            files.forEach(file => {
                if (file.size <= maxSize) {
                    validFiles.push(file);
                } else {
                    invalidFiles.push(file.name);
                }
            });
            
            if (invalidFiles.length > 0) {
                alert(`The following files exceed 10MB limit and will be skipped:\n${invalidFiles.join('\n')}`);
            }
            
            if (validFiles.length > 0) {
                setData('documents', validFiles);
            }
        }
    };

    const removeFile = (index) => {
        const newFiles = data.documents.filter((_, i) => i !== index);
        setData('documents', newFiles);
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
                                Upload Documents <span className="text-danger-600">*</span>
                            </label>
                            <input
                                type="file"
                                multiple
                                onChange={handleFileChange}
                                className="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                required
                            />
                            {errors.documents && (
                                <p className="mt-1 text-sm text-danger-600">{errors.documents}</p>
                            )}
                            <p className="mt-1 text-sm text-gray-500">
                                Maximum file size: 10MB per file. Maximum 10 files at once. You can select multiple files at once.
                            </p>
                        </div>

                        {data.documents && data.documents.length > 0 && (
                            <div className="space-y-2">
                                <p className="text-sm font-medium text-gray-700">
                                    Selected Files ({data.documents.length}):
                                </p>
                                <div className="space-y-2 max-h-40 overflow-y-auto">
                                    {data.documents.map((file, index) => (
                                        <div key={index} className="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                            <div className="flex-1 min-w-0">
                                                <p className="text-sm text-blue-800 font-medium truncate">
                                                    {file.name}
                                                </p>
                                                <p className="text-xs text-blue-600">
                                                    Size: {(file.size / 1024).toFixed(2)} KB
                                                </p>
                                            </div>
                                            <button
                                                type="button"
                                                onClick={() => removeFile(index)}
                                                className="ml-2 text-red-600 hover:text-red-800 text-sm font-medium"
                                            >
                                                Remove
                                            </button>
                                        </div>
                                    ))}
                                </div>
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
                        {processing ? 'Uploading...' : `Upload ${data.documents.length > 0 ? data.documents.length : ''} Document${data.documents.length > 1 ? 's' : ''}`}
                    </Button>
                </ModalFooter>
            </form>
        </Modal>
    );
}

