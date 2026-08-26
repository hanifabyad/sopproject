# e-QMS Current Project State

## Latest e-QMS Updates & Implementation Details

### 1. Workflow Approval
* **Automated Workflow**: Workflow is now fully automated, removing the manual hand-off (estafet) function.
* **Reviewer Sequence**: Approval runs strictly based on the defined reviewer order.
* **Final Approver**: Zikri is the primary final approver and is always placed at the end of the workflow sequence.
* **Optional Approvers**: Added a compact "Tambah Pengesah" button to add optional signers via a non-intrusive modal that preserves vertical space.
* **Dynamic Logo Page**: Added a dedicated page `/admin/logo` linked from the dashboard header cards to manage PT names, addresses, and logos dynamically.
* **Dual Roles per Email**: A single user account can now hold two approval titles under the same email.
* **Hendro's Accounts**: Created user `hendro` (email `pbl.512hebat+hendro@gmail.com`, role `Ka. BU CPT`) and `hendro_dir` (email `pbl.512hebat+hendro_dir@gmail.com`, role `Direktur CPT`) to allow distinct signatures and roles.

### 2. Approval User & Magic Link
* **Magic Link UI**: Adjusted approval page border-radius to be less rounded.
* **Unauthorized Bug Fixed**: Resolved the unauthorized page error after a creator resubmits a revision requested via email.
* **Revision Delivery**: Revision files are properly sent and delivered to the reviewers' email inbox.
* **Dual Stamps**: Users holding two separate roles will have both stamps generated sequentially based on their roles.
* **Self-Reviewer Stamp Bug**: Fixed stamp overlay issues where a user acts as both the creator and reviewer.
* **UI/UX Consistency**: Unified action buttons and row icons across Business Unit (BU) and Support sections. All "Buka Dokumen" buttons now share the same size, charcoal-black theme, and Phosphor icons. All document list row icons are styled in a premium dark/gold theme.
* **Unified Screen Layout**: Replaced the reviewer's flex layout (which used a fixed 400px width for the form, shrinking the PDF viewport at smaller resolutions) with the exact same 12-column grid layout as the Admin view (8 cols for PDF, 4 cols for sidebar). The layout split is now 100% consistent across both views.

### 3. Lembar Pengesahan (LP) & PDF
* **LP Generation**: Lembar Pengesahan is automatically generated for SOP documents.
* **PT CPT Format**: Full support added for PT CPT-specific layouts.
* **DCC Stamp Labels**: 
  * CPT documents use the label `"DCC CPT"`.
  * PKM Group documents use the label `"DCC PKM GROUP"`.
* **Page Count Normalization**: The cover page is excluded from the document page count. Page numbering begins directly on the Lembar Pengesahan page.
* **Multiple Signers**: Supports multiple approvers on the LP.
* **Stamp Positioning**: Positions are automatically calculated based on the sequential appearance of the signers, including cases with multi-role users.

### 4. CPT Document Revision Control
* **Revision 0 Initial State**: The initial document uploaded automatically starts as Revision 0 with its creation date.
* **Revision Increments**: Revision numbers increment to 1, 2, 3, etc., only when a new revision file is actually uploaded.
* **No Reviewer Penalty**: Reviewer actions (approvals or revision requests) do not trigger revision number changes.
* **History Preservation**: Previous revision dates and records are fully preserved in the database.
* **Default Range Display**: The CPT table displays default placeholders from 0 to 7.
* **Distinct Review Requests**: Reviewer revision requests are separated from the file upload action to prevent erroneous increments.

### 5. Approval History Layout
* **"Riwayat Selesai" Page**: Redesigned to be highly responsive and readable.
* **Zoom Optimization**: Fully optimized to fit browser zoom levels up to 125% without overflowing.
* **Layout Boundaries**: Table cells and boundaries wrap correctly without leaking off-screen.
* **Table Spacing**: Compacted space between the number column and the Document SOP title.
* **Adaptive Columns**: Department/Unit column auto-hides on narrow viewports.
* **Responsive Buttons**: Action buttons shrink appropriately on smaller devices.

### 6. Reviewer View Interface
* **Header Typography**: Reduced scale of "Document Stream Player", "Pratinjau Sah", and "Tab Baru" links to balance the PDF viewer.
* **No Wrap Labels**: Long department labels (e.g. `"KEUANGAN & ACCOUNTING"`) stay on a single line and do not truncate awkwardly.
* **Standby Alert Removed**: The "Sistem Siaga Review" block has been completely removed from the reviewer pending queue.
* **Unified Icons**: Standardized all UI icons using Material Symbols.
* **Navigation Animation**: Solved the browser Back button issue that caused temporary white screens during page transitions.

### 7. SOP Upload Form
* **Legacy Elements Removed**: Cleaned up the old workflow information panel.
* **Form Centricity**: Focused the UI elements entirely on the core inputs.
* **Placeholder Consistency**: Standardized and removed confusing example values in the Business Unit and Support form inputs.
* **LP Generator Panel**: Deleted unnecessary LP generator panels from the creation view.

### 8. Validation & Testing
* **Blade Cache**: Active and verified.
* **E2E & QA Validation**:
  * `E2EWorkflowTest` passes.
  * `QAWorkflowAuditTest` passes.
  * All custom workflows (including CPT/HC) function correctly.
* **Test Metrics**:
  * **E2E**: 3 tests passed, 49 assertions.
  * **QA**: 3 tests passed, 10 assertions.
  * **Total Test Suite**: 10 tests passed, 86 assertions.
