# Document Folders Feature

## Overview
Implemented a folder-based organization system for lead documents, grouping them by document type for better organization and easier navigation.

## Features

### 📁 Folder Organization
- Documents are automatically grouped by their `kind` (type)
- Each document type gets its own folder
- Only folders with documents are shown

### 🎨 Visual Design
- **Folder Icons**: Yellow folder icons for visual clarity
  - Closed folder icon when collapsed
  - Open folder icon when expanded
- **Chevron Indicators**: Arrow icons showing expand/collapse state
- **File Count Badge**: Shows number of files in each folder
- **Hover Effects**: Smooth transitions on folder headers

### 🔄 Interactive Features
- **Click to Expand/Collapse**: Click anywhere on folder header to toggle
- **Expandable State**: Each folder can be independently expanded or collapsed
- **Document Table**: When expanded, shows detailed document information:
  - Document name with file icon
  - File size in KB
  - Upload date
  - Download button

### 📂 Document Types (Folders)
The system recognizes and labels these document types:
- **Survey Pictures** (`survey_pics`)
- **Floor Plan** (`floor_plan`)
- **Benefit Proof** (`benefit_proof`)
- **Gas Meter** (`gas_meter`)
- **EPR Report** (`epr_report`)
- **EPC** (`epc`)
- **Other Documents** (`other`)

### 🎯 User Experience
1. **Empty State**: Shows a friendly message with folder icon when no documents exist
2. **Organized View**: Documents grouped logically by type
3. **Quick Access**: Click folder to expand and see all documents of that type
4. **Space Efficient**: Collapsed folders save screen space
5. **Visual Feedback**: Hover states and smooth transitions

## Technical Implementation

### Frontend Changes (`resources/js/Pages/Leads/Show.jsx`)

#### Added Icons
```javascript
import { FolderIcon, FolderOpenIcon, ChevronRightIcon, ChevronDownIcon } from '@heroicons/react/24/outline';
```

#### State Management
```javascript
const [expandedFolders, setExpandedFolders] = useState({});
```

#### Helper Functions
1. **`groupDocumentsByKind()`**: Groups documents array by their `kind` property
2. **`formatDocumentKind(kind)`**: Converts kind codes to readable labels
3. **`toggleFolder(kind)`**: Manages expand/collapse state per folder

#### UI Structure
```
Documents Card
└── For each document type (folder):
    ├── Folder Header (clickable)
    │   ├── Folder Icon (open/closed)
    │   ├── Chevron Icon (right/down)
    │   ├── Folder Name
    │   ├── File Count
    │   └── Expand/Collapse Hint
    └── Folder Contents (when expanded)
        └── Documents Table
            ├── Name column (with file icon)
            ├── Size column
            ├── Uploaded date column
            └── Actions column (Download button)
```

## Code Example

### Grouping Logic
```javascript
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
```

### Folder Rendering
```javascript
{Object.entries(groupDocumentsByKind()).map(([kind, documents]) => (
    <div key={kind} className="border border-gray-200 rounded-lg overflow-hidden">
        {/* Folder Header - clickable */}
        <div onClick={() => toggleFolder(kind)}>
            {/* Folder icon, name, count */}
        </div>
        
        {/* Folder Contents - conditional */}
        {expandedFolders[kind] && (
            <table>
                {/* Document rows */}
            </table>
        )}
    </div>
))}
```

## Usage

### For Users
1. Navigate to a lead's detail page
2. Scroll to the **Documents** section
3. See folders for each document type that has files
4. Click on a folder to expand and view documents
5. Click again to collapse
6. Click **Download** to download any document

### For Developers
The feature automatically:
- Groups documents by their `kind` field
- Creates folders only for types that have documents
- Handles empty states gracefully
- Maintains expand/collapse state per folder

## Benefits

✅ **Better Organization**: Documents grouped by type instead of flat list  
✅ **Easier Navigation**: Find documents faster by type  
✅ **Cleaner UI**: Collapsed folders reduce clutter  
✅ **Scalable**: Works well with few or many documents  
✅ **Intuitive**: Familiar folder metaphor for users  
✅ **Responsive**: Works on all screen sizes  

## Future Enhancements (Optional)

- Add ability to delete documents from folder view
- Bulk download entire folder
- Drag & drop to change document type
- Search/filter within folders
- Sort documents within folders
- Show folder file size totals
- Persist expanded state in localStorage
- Add document preview on click

## Testing

To test the feature:
1. Create/view a lead with multiple documents
2. Upload documents of different types
3. Verify folders appear for each type
4. Test expand/collapse functionality
5. Verify download buttons work
6. Test with empty state (no documents)

## Deployment

The feature is ready to deploy:
```bash
# Already built in local build
npm run build

# Push to GitHub for auto-deployment
git add .
git commit -m "Add folder-based document organization"
git push origin main
```

The documents section now provides a modern, organized, and user-friendly way to manage lead documents! 📂✨

