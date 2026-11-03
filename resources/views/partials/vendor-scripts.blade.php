<!-- Simplebar JS -->
<script  src="{{URL::asset('build/libs/simplebar/simplebar.min.js')}}"></script>

<!-- Preline JS -->
<script src="{{URL::asset('build/libs/preline/preline.js')}}"></script>

@if (Route::is(['/', 'chart-apex', 'crm-dashboard', 'finance-dashboard', 'index', 'inventory-dashboard', 'layout-fullwidth', 'layout-hidden', 'layout-hoverview', 'layout-mini', 'layout-rtl', 'pos-dashboard', 'procurement-dashboard', 'project-dashboard', 'sales-dashboard', 'support-dashboard', 'ui-lightbox']))
<!-- Apexcharts JS -->
<script src="{{URL::asset('build/libs/apexcharts/apexcharts.min.js')}}"></script>
@vite('resources/js/apex-chart-data.js')
@endif

@if (Route::is(['accounts-payable', 'accounts-receivable', 'add-cash-sales', 'add-credit-notes-invoice', 'add-delivery-notes-invoice', 'add-employee', 'add-job-requisition', 'add-payroll', 'add-purchase', 'add-purchase-order', 'add-purchase-return', 'add-recurring-invoice', 'add-sales', 'add-sales-quotes', 'asset-register', 'assignments', 'attendance', 'audit-logs', 'bank-reconciliation', 'bill-of-materials', 'calendar', 'cash-sales', 'cashflow', 'categories', 'chart-of-accounts', 'compliance-docs', 'contact-details', 'contact-messages', 'contacts', 'contacts-grid', 'contracts', 'credit-notes', 'crm-dashboard', 'deal-details', 'deals', 'deals-grid', 'delete-requests', 'delivery-notes', 'disposal', 'document-repository', 'document-workflows', 'edit-cash-sales', 'edit-credit-notes-invoice', 'edit-delivery-notes-invoice', 'edit-employee', 'edit-job-requisition', 'edit-payroll', 'edit-purchase', 'edit-purchase-order', 'edit-purchase-return', 'edit-purchases', 'edit-recurring-invoice', 'edit-sales', 'edit-sales-quotes', 'employee-portal', 'employees', 'expense-summary', 'expenses', 'finance-dashboard', 'form-elements', 'form-pickers', 'holidays', 'income-expense', 'income-summary', 'interview-scheduler', 'inventory', 'inventory-dashboard', 'journal-entries', 'lead-details', 'leave', 'maintenance', 'manage-stock', 'milestones', 'notes', 'payments', 'payroll', 'performance-appraisal', 'policies-manuals', 'pos-dashboard', 'procurement-analytics', 'procurement-dashboard', 'products', 'project-dashboard', 'projects', 'projects-grid', 'purchase-orders', 'purchase-return', 'purchases', 'quality-inspections', 'recurring-invoices', 'refunds', 'resource-allocation', 'rfq-management', 'roles-permissions', 'sales-dashboard', 'sales-orders', 'sales-quotes', 'shipments', 'sla-management', 'stock-adjustment', 'stock-transfer', 'support-dashboard', 'tasks', 'tasks-grid', 'tax-summary', 'tickets', 'timesheet', 'todo', 'training-development', 'transactions', 'user-management', 'vendors', 'warehouse', 'work-orders', 'workflow-actions', 'workflow-requests']))
<!-- Flatpickr JS -->
<script src="{{URL::asset('build/libs/flatpickr/flatpickr.min.js')}}"></script>
@endif

@if (Route::is(['sales-dashboard']))
<!-- EChart JS -->
<script src="{{URL::asset('build/libs/echarts/echarts.min.js')}}"></script>
<script src="{{URL::asset('build/libs/echarts-countries-js/echarts-countries-js/world.js')}}"></script>
@vite('resources/js/echart-chart-data.js')
@endif

@if (Route::is(['calendar']))
<!-- Fullcalendar JS (Core + DayGrid + Interaction) -->
<script src="{{URL::asset('build/libs/@fullcalendar/core/index.global.min.js')}}"></script>
<script src="{{URL::asset('build/libs/@fullcalendar/daygrid/index.global.min.js')}}"></script>
<script src="{{URL::asset('build/libs/@fullcalendar/interaction/index.global.min.js')}}"></script>
@vite('resources/js/calendar-data.js')
@endif

@if (Route::is(['form-select2']))
<!-- Choices Js -->
<script src="{{URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js')}}"></script>
@endif

@if (Route::is(['ui-rangeslider']))

<!-- Wnumb JS -->
<script src="{{URL::asset('build/libs/wnumb/wNumb.min.js')}}"></script>

<!-- noUiSlider js -->
<script src="{{URL::asset('build/libs/nouislider/nouislider.min.js')}}"></script>

<!-- Rangeslider JS -->
@vite('resources/js/range-slider.js')
@endif

@if (Route::is(['form-editors']))
<!-- Quill Editor JS -->
<script src="{{URL::asset('build/libs/quill/quill.js')}}"></script>

@vite('resources/js/form-quill.js')
@endif

@if (Route::is(['chat']))
<!-- Chat JS -->
@vite('resources/js/chat.js')
@endif

@if (Route::is(['coming-soon']))
<!-- Coming Soon JS -->
@vite('resources/js/coming-soon.js')
@endif

@if (Route::is(['chart-js']))
<!-- Chart JS -->
<script src="{{URL::asset('build/libs/chart.js/chart.umd.min.js')}}"></script>
@vite('resources/js/charjs-chart-data.js')
@endif

@if (Route::is(['data-tables']))
<!-- jQuery FIRST -->
<script src="{{URL::asset('build/libs/jquery/jquery.min.js')}}"></script>

<!-- Then DataTables JS -->
<script src="{{URL::asset('build/libs/datatables.net/js/dataTables.min.js')}}"></script>

<!-- Finally your custom init -->
@vite('resources/js/datatable.js')
@endif
@if (Route::is(['ui-dragula']))
<!-- Dragula Js-->
<script src="{{URL::asset('build/libs/dragula/dragula.min.js')}}"></script>
@vite('resources/js/dragula.js')
@endif

@if (Route::is(['ui-clipboard']))
<!-- Clipboard JS -->
<script src="{{URL::asset('build/libs/clipboard/clipboard.min.js')}}"></script>
@vite('resources/js/clipboard.js')
@endif

@if (Route::is(['ui-collapse', 'ui-lightbox']))
<!-- Glightbox JS -->
<script src="{{URL::asset('build/libs/glightbox/js/glightbox.min.js')}}"></script>
@vite('resources/js/lightbox.js')
@endif

<!-- Custom JS -->
@vite('resources/js/script.js')
