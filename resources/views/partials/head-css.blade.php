<!-- Favicon -->
<link rel="shortcut icon" type="image/x-icon" href="{{URL::asset('build/img/favicon.png')}}">

<!-- Apple Touch Icon -->
<link rel="apple-touch-icon" sizes="180x180" href="{{URL::asset('build/img/apple-icon.png')}}">

<!-- Theme Script -->
@vite('resources/js/theme-script.js')

<!-- Phosphor CSS -->
<link rel="stylesheet" href="{{URL::asset('build/libs/@phosphor-icons/web/duotone/style.css')}}">
<link rel="stylesheet" href="{{URL::asset('build/libs/@phosphor-icons/web/regular/style.css')}}">
<link rel="stylesheet" href="{{URL::asset('build/libs/@phosphor-icons/web/fill/style.css')}}">

<!-- Lucide CSS -->
<link rel="stylesheet" href="{{URL::asset('build/libs/lucide-static/font/lucide.css')}}">

<!-- Simplebar CSS -->
<link rel="stylesheet" href="{{URL::asset('build/libs/simplebar/simplebar.min.css')}}">

@if (Route::is(['form-select2']))
<!-- Choices CSS -->
<link rel="stylesheet" href="{{URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css')}}">
@endif

@if (Route::is(['ui-rangeslider']))
<!-- Rangeslider CSS -->
<link rel="stylesheet" href="{{URL::asset('build/libs/nouislider/nouislider.min.css')}}">
@endif

<!-- Prism CSS -->
<link rel="stylesheet" href="{{URL::asset('build/libs/prismjs/themes/prism.min.css')}}">

@if (Route::is(['icon-tabler']))
<link rel="stylesheet" href="{{URL::asset('build/libs/@tabler/icons-webfont/tabler-icons.min.css')}}">
@endif

@if (Route::is(['form-editors']))
<!-- Quill css -->
<link rel="stylesheet" href="{{URL::asset('build/libs/quill/quill.core.css')}}">
<link rel="stylesheet" href="{{URL::asset('build/libs/quill/quill.snow.css')}}">
<link rel="stylesheet" href="{{URL::asset('build/libs/quill/quill.bubble.css')}}">
@endif

<!-- Fontawesome CSS -->
<link rel="stylesheet" href="{{URL::asset('build/libs/@fortawesome/fontawesome-free/css/fontawesome.min.css')}}">
<link rel="stylesheet" href="{{URL::asset('build/libs/@fortawesome/fontawesome-free/css/all.min.css')}}">

@if (Route::is(['accounts-payable', 'accounts-receivable', 'add-cash-sales', 'add-credit-notes-invoice', 'add-delivery-notes-invoice', 'add-employee', 'add-job-requisition', 'add-payroll', 'add-purchase', 'add-purchase-order', 'add-purchase-return', 'add-recurring-invoice', 'add-sales', 'add-sales-quotes', 'asset-register', 'assignments', 'attendance', 'audit-logs', 'bank-reconciliation', 'bill-of-materials', 'calendar', 'cash-sales', 'cashflow', 'categories', 'chart-of-accounts', 'compliance-docs', 'contact-details', 'contact-messages', 'contacts', 'contacts-grid', 'contracts', 'credit-notes', 'crm-dashboard', 'deal-details', 'deals', 'deals-grid', 'delete-requests', 'delivery-notes', 'disposal', 'document-repository', 'document-workflows', 'edit-cash-sales', 'edit-credit-notes-invoice', 'edit-delivery-notes-invoice', 'edit-employee', 'edit-job-requisition', 'edit-payroll', 'edit-purchase', 'edit-purchase-order', 'edit-purchase-return', 'edit-purchases', 'edit-recurring-invoice', 'edit-sales', 'edit-sales-quotes', 'employee-portal', 'employees', 'expense-summary', 'expenses', 'finance-dashboard', 'form-elements', 'form-pickers', 'holidays', 'income-expense', 'income-summary', 'interview-scheduler', 'inventory', 'inventory-dashboard', 'journal-entries', 'lead-details', 'leave', 'maintenance', 'manage-stock', 'milestones', 'notes', 'payments', 'payroll', 'performance-appraisal', 'policies-manuals', 'pos-dashboard', 'procurement-analytics', 'procurement-dashboard', 'products', 'project-dashboard', 'projects', 'projects-grid', 'purchase-orders', 'purchase-return', 'purchases', 'quality-inspections', 'recurring-invoices', 'refunds', 'resource-allocation', 'rfq-management', 'roles-permissions', 'sales-dashboard', 'sales-orders', 'sales-quotes', 'shipments', 'sla-management', 'stock-adjustment', 'stock-transfer', 'support-dashboard', 'tasks', 'tasks-grid', 'tax-summary', 'tickets', 'timesheet', 'todo', 'training-development', 'transactions', 'user-management', 'vendors', 'warehouse', 'work-orders', 'workflow-actions', 'workflow-requests']))
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="{{URL::asset('build/libs/flatpickr/flatpickr.min.css')}}">
<link rel="stylesheet" href="{{URL::asset('build/libs/flatpickr/flatpickr.css')}}">
@endif

@if (Route::is(['ui-dragula']))
<!-- Dragula CSS -->
<link rel="stylesheet" href="{{URL::asset('build/libs/dragula/dragula.min.css')}}">
@endif

@if (Route::is(['ui-lightbox']))
<!-- Glightbox CSS -->
<link rel="stylesheet" href="{{URL::asset('build/libs/glightbox/css/glightbox.min.css')}}">
<link rel="stylesheet" href="{{URL::asset('build/libs/glightbox/css/glightbox.css')}}">
@endif

@if (Route::is(['data-tables']))
<!-- DataTables CSS -->
<link rel="stylesheet" href="{{URL::asset('build/libs/datatables.net-dt/css/dataTables.dataTables.min.css')}}">
@endif

<!-- Main CSS -->
@vite('resources/css/style.css')
