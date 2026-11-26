<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Master\UserController;
use App\Models\Master\MasterProductSpec;
use App\Http\Middleware\RequireAuthentication;
use Illuminate\Support\Facades\Route;

require __DIR__.'/web/cut-board.php';
require __DIR__.'/web/product-catalog.php';

Route::middleware(RequireAuthentication::class)->group(function () {

Route::get('/', function () {
    return redirect()->route('wms.cut-boards');
})->name('index');

Route::get('/index', function () {
    return redirect()->route('wms.cut-boards');
})->name('index');

Route::get('/accounts-payable', function () {
    return view('accounts-payable');
})->name('accounts-payable');

Route::get('/accounts-receivable', function () {
    return view('accounts-receivable');
})->name('accounts-receivable');

Route::get('/add-cash-sales', function () {
    return view('add-cash-sales');
})->name('add-cash-sales');

Route::get('/add-credit-notes-invoice', function () {
    return view('add-credit-notes-invoice');
})->name('add-credit-notes-invoice');

Route::get('/add-delivery-notes-invoice', function () {
    return view('add-delivery-notes-invoice');
})->name('add-delivery-notes-invoice');

Route::get('/add-employee', function () {
    return view('add-employee');
})->name('add-employee');

Route::get('/add-job-requisition', function () {
    return view('add-job-requisition');
})->name('add-job-requisition');

Route::get('/add-membership-plan', function () {
    return view('add-membership-plan');
})->name('add-membership-plan');

Route::get('/add-payroll', function () {
    return view('add-payroll');
})->name('add-payroll');

Route::get('/add-product', function () {
    return view('add-product');
})->name('add-product');

Route::get('/add-purchase-order', function () {
    return view('add-purchase-order');
})->name('add-purchase-order');

Route::get('/add-purchase-return', function () {
    return view('add-purchase-return');
})->name('add-purchase-return');

Route::get('/add-purchase', function () {
    return view('add-purchase');
})->name('add-purchase');

Route::get('/add-recurring-invoice', function () {
    return view('add-recurring-invoice');
})->name('add-recurring-invoice');

Route::get('/add-sales-quotes', function () {
    return view('add-sales-quotes');
})->name('add-sales-quotes');

Route::get('/add-sales', function () {
    return view('add-sales');
})->name('add-sales');

Route::get('/asset-analytics', function () {
    return view('asset-analytics');
})->name('asset-analytics');

Route::get('/asset-register', function () {
    return view('asset-register');
})->name('asset-register');

Route::get('/assignments', function () {
    return view('assignments');
})->name('assignments');

Route::get('/attendance', function () {
    return view('attendance');
})->name('attendance');

Route::get('/audit-logs', function () {
    return view('audit-logs');
})->name('audit-logs');

Route::get('/bank-reconciliation', function () {
    return view('bank-reconciliation');
})->name('bank-reconciliation');

Route::get('/barcode-print', function () {
    return view('barcode-print');
})->name('barcode-print');

Route::get('/bill-of-materials', function () {
    return view('bill-of-materials');
})->name('bill-of-materials');

Route::get('/blank-page', function () {
    return view('blank-page');
})->name('blank-page');

Route::get('/brands', function () {
    return view('brands');
})->name('brands');

Route::get('/budgeting', function () {
    return view('budgeting');
})->name('budgeting');

Route::get('/calendar', function () {
    return view('calendar');
})->name('calendar');

Route::get('/campaigns', function () {
    return view('campaigns');
})->name('campaigns');

Route::get('/candidate-pipline', function () {
    return view('candidate-pipline');
})->name('candidate-pipline');

Route::get('/cash-sales-details', function () {
    return view('cash-sales-details');
})->name('cash-sales-details');

Route::get('/cash-sales', function () {
    return view('cash-sales');
})->name('cash-sales');

Route::get('/cashflow', function () {
    return view('cashflow');
})->name('cashflow');

Route::get('/categories', function () {
    return view('categories');
})->name('categories');

Route::get('/chart-apex', function () {
    return view('chart-apex');
})->name('chart-apex');

Route::get('/chart-js', function () {
    return view('chart-js');
})->name('chart-js');

Route::get('/chart-of-accounts', function () {
    return view('chart-of-accounts');
})->name('chart-of-accounts');

Route::get('/chat', function () {
    return view('chat');
})->name('chat');

Route::get('/coming-soon', function () {
    return view('coming-soon');
})->name('coming-soon');

Route::get('/compliance-docs', function () {
    return view('compliance-docs');
})->name('compliance-docs');

Route::get('/contact-details', function () {
    return view('contact-details');
})->name('contact-details');

Route::get('/contact-messages', function () {
    return view('contact-messages');
})->name('contact-messages');

Route::get('/contacts-grid', function () {
    return view('contacts-grid');
})->name('contacts-grid');

Route::get('/contacts', function () {
    return view('contacts');
})->name('contacts');

Route::get('/contracts', function () {
    return view('contracts');
})->name('contracts');

Route::get('/credit-notes-details', function () {
    return view('credit-notes-details');
})->name('credit-notes-details');

Route::get('/credit-notes', function () {
    return view('credit-notes');
})->name('credit-notes');

Route::get('/crm-dashboard', function () {
    return view('crm-dashboard');
})->name('crm-dashboard');

Route::get('/customer-analytics', function () {
    return view('customer-analytics');
})->name('customer-analytics');

Route::get('/customer-feedback', function () {
    return view('customer-feedback');
})->name('customer-feedback');

Route::get('/customers', function () {
    return view('customers');
})->name('customers');

Route::get('/data-tables', function () {
    return view('data-tables');
})->name('data-tables');

Route::get('/deal-details', function () {
    return view('deal-details');
})->name('deal-details');

Route::get('/deals-grid', function () {
    return view('deals-grid');
})->name('deals-grid');

Route::get('/deals', function () {
    return view('deals');
})->name('deals');

Route::get('/delete-requests', function () {
    return view('delete-requests');
})->name('delete-requests');

Route::get('/delivery-note-details', function () {
    return view('delivery-note-details');
})->name('delivery-note-details');

Route::get('/delivery-notes', function () {
    return view('delivery-notes');
})->name('delivery-notes');

Route::get('/departments', function () {
    return view('departments');
})->name('departments');

Route::get('/depreciation', function () {
    return view('depreciation');
})->name('depreciation');

Route::get('/designations-roles', function () {
    return view('designations-roles');
})->name('designations-roles');

Route::get('/disposal', function () {
    return view('disposal');
})->name('disposal');

Route::get('/document-repository', function () {
    return view('document-repository');
})->name('document-repository');

Route::get('/document-workflows', function () {
    return view('document-workflows');
})->name('document-workflows');

Route::get('/edit-cash-sales', function () {
    return view('edit-cash-sales');
})->name('edit-cash-sales');

Route::get('/edit-credit-notes-invoice', function () {
    return view('edit-credit-notes-invoice');
})->name('edit-credit-notes-invoice');

Route::get('/edit-delivery-notes-invoice', function () {
    return view('edit-delivery-notes-invoice');
})->name('edit-delivery-notes-invoice');

Route::get('/edit-employee', function () {
    return view('edit-employee');
})->name('edit-employee');

Route::get('/edit-job-requisition', function () {
    return view('edit-job-requisition');
})->name('edit-job-requisition');

Route::get('/edit-payroll', function () {
    return view('edit-payroll');
})->name('edit-payroll');

Route::get('/edit-product', function () {
    return view('edit-product');
})->name('edit-product');

Route::get('/edit-profile', function () {
    return view('edit-profile');
})->name('edit-profile');

Route::get('/edit-purchase-order', function () {
    return view('edit-purchase-order');
})->name('edit-purchase-order');

Route::get('/edit-purchase-return', function () {
    return view('edit-purchase-return');
})->name('edit-purchase-return');

Route::get('/edit-purchase', function () {
    return view('edit-purchase');
})->name('edit-purchase');

Route::get('/edit-recurring-invoice', function () {
    return view('edit-recurring-invoice');
})->name('edit-recurring-invoice');

Route::get('/edit-sales-quotes', function () {
    return view('edit-sales-quotes');
})->name('edit-sales-quotes');

Route::get('/edit-sales', function () {
    return view('edit-sales');
})->name('edit-sales');

Route::get('/email-compose', function () {
    return view('email-compose');
})->name('email-compose');

Route::get('/email-verification-cover', function () {
    return view('email-verification-cover');
})->name('email-verification-cover');

Route::get('/email-verification-illustration', function () {
    return view('email-verification-illustration');
})->name('email-verification-illustration');

Route::get('/email-verification', function () {
    return view('email-verification');
})->name('email-verification');

Route::get('/email', function () {
    return view('email');
})->name('email');

Route::get('/employee-details', function () {
    return view('employee-details');
})->name('employee-details');

Route::get('/employee-portal', function () {
    return view('employee-portal');
})->name('employee-portal');

Route::get('/employees-grid', function () {
    return view('employees-grid');
})->name('employees-grid');

Route::get('/employees', function () {
    return view('employees');
})->name('employees');

Route::get('/error-404', function () {
    return view('error-404');
})->name('error-404');

Route::get('/error-500', function () {
    return view('error-500');
})->name('error-500');

Route::get('/expense-category', function () {
    return view('expense-category');
})->name('expense-category');

Route::get('/expense-summary', function () {
    return view('expense-summary');
})->name('expense-summary');

Route::get('/expenses', function () {
    return view('expenses');
})->name('expenses');

Route::get('/file-manager', function () {
    return view('file-manager');
})->name('file-manager');

Route::get('/finance-dashboard', function () {
    return view('finance-dashboard');
})->name('finance-dashboard');

Route::get('/forgot-password-cover', function () {
    return view('forgot-password-cover');
})->name('forgot-password-cover');

Route::get('/forgot-password-illustration', function () {
    return view('forgot-password-illustration');
})->name('forgot-password-illustration');

Route::get('/forgot-password', function () {
    return view('forgot-password');
})->name('forgot-password');

Route::get('/form-editors', function () {
    return view('form-editors');
})->name('form-editors');

Route::get('/form-elements', function () {
    return view('form-elements');
})->name('form-elements');

Route::get('/form-pickers', function () {
    return view('form-pickers');
})->name('form-pickers');

Route::get('/form-select2', function () {
    return view('form-select2');
})->name('form-select2');

Route::get('/general-settings', function () {
    return view('general-settings');
})->name('general-settings');

Route::get('/holidays', function () {
    return view('holidays');
})->name('holidays');

Route::get('/hr-analytics', function () {
    return view('hr-analytics');
})->name('hr-analytics');

Route::get('/icon-fontawesome', function () {
    return view('icon-fontawesome');
})->name('icon-fontawesome');

Route::get('/icon-lucide', function () {
    return view('icon-lucide');
})->name('icon-lucide');

Route::get('/icon-phosphor', function () {
    return view('icon-phosphor');
})->name('icon-phosphor');

Route::get('/icon-tabler', function () {
    return view('icon-tabler');
})->name('icon-tabler');

Route::get('/income-expense', function () {
    return view('income-expense');
})->name('income-expense');

Route::get('/income-summary', function () {
    return view('income-summary');
})->name('income-summary');

Route::get('/integrations-settings', function () {
    return view('integrations-settings');
})->name('integrations-settings');

Route::get('/interview-scheduler', function () {
    return view('interview-scheduler');
})->name('interview-scheduler');

Route::get('/inventory-dashboard', function () {
    return view('inventory-dashboard');
})->name('inventory-dashboard');

Route::get('/inventory', function () {
    return view('inventory');
})->name('inventory');

Route::get('/invoice-templates', function () {
    return view('invoice-templates');
})->name('invoice-templates');

Route::get('/journal-entries', function () {
    return view('journal-entries');
})->name('journal-entries');

Route::get('/knowledge-base-details', function () {
    return view('knowledge-base-details');
})->name('knowledge-base-details');

Route::get('/knowledge-base', function () {
    return view('knowledge-base');
})->name('knowledge-base');

Route::get('/layout-fullwidth', function () {
    return view('layout-fullwidth');
})->name('layout-fullwidth');

Route::get('/layout-hidden', function () {
    return view('layout-hidden');
})->name('layout-hidden');

Route::get('/layout-hoverview', function () {
    return view('layout-hoverview');
})->name('layout-hoverview');

Route::get('/layout-mini', function () {
    return view('layout-mini');
})->name('layout-mini');

Route::get('/layout-rtl', function () {
    return view('layout-rtl');
})->name('layout-rtl');

Route::get('/lead-details', function () {
    return view('lead-details');
})->name('lead-details');

Route::get('/leads-grid', function () {
    return view('leads-grid');
})->name('leads-grid');

Route::get('/leads', function () {
    return view('leads');
})->name('leads');

Route::get('/leave-types', function () {
    return view('leave-types');
})->name('leave-types');

Route::get('/leave', function () {
    return view('leave');
})->name('leave');

Route::get('/lock-screen', function () {
    return view('lock-screen');
})->name('lock-screen');

Route::get('/login-cover', function () {
    return view('login-cover');
})->name('login-cover');

Route::get('/login-illustration', function () {
    return view('login-illustration');
})->name('login-illustration');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('register.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/maintenance', function () {
    return view('maintenance');
})->name('maintenance');

Route::get('/manage-stock', function () {
    return view('manage-stock');
})->name('manage-stock');

Route::get('/membership-addons', function () {
    return view('membership-addons');
})->name('membership-addons');

Route::get('/membership-plans', function () {
    return view('membership-plans');
})->name('membership-plans');

Route::get('/milestones', function () {
    return view('milestones');
})->name('milestones');

Route::get('/notes', function () {
    return view('notes');
})->name('notes');

Route::get('/notifications-settings', function () {
    return view('notifications-settings');
})->name('notifications-settings');

Route::get('/offer-management', function () {
    return view('offer-management');
})->name('offer-management');

Route::get('/onboarding', function () {
    return view('onboarding');
})->name('onboarding');

Route::get('/payments', function () {
    return view('payments');
})->name('payments');

Route::get('/payroll', function () {
    return view('payroll');
})->name('payroll');

Route::get('/performance-appraisal', function () {
    return view('performance-appraisal');
})->name('performance-appraisal');

Route::get('/pipeline', function () {
    return view('pipeline');
})->name('pipeline');

Route::get('/policies-manuals', function () {
    return view('policies-manuals');
})->name('policies-manuals');

Route::get('/pos-dashboard', function () {
    return view('pos-dashboard');
})->name('pos-dashboard');

Route::get('/pos-orders', function () {
    return view('pos-orders');
})->name('pos-orders');

Route::get('/pos', function () {
    return view('pos');
})->name('pos');

Route::get('/print-settings', function () {
    return view('print-settings');
})->name('print-settings');

Route::get('/procurement-analytics', function () {
    return view('procurement-analytics');
})->name('procurement-analytics');

Route::get('/procurement-dashboard', function () {
    return view('procurement-dashboard');
})->name('procurement-dashboard');

Route::get('/products', function () {
    $query = MasterProductSpec::query();

    if (request()->filled('q')) {
        $term = request()->string('q')->toString();
        $query->where(fn ($builder) => $builder
            ->where('code', 'like', "%{$term}%")
            ->orWhere('name', 'like', "%{$term}%")
            ->orWhere('brand', 'like', "%{$term}%"));
    }

    return view('products', ['items' => $query->orderByDesc('id')->paginate(20)->withQueryString()]);
})->name('products');

Route::get('/profile', function () {
    return view('profile');
})->name('profile');

Route::get('/profit-loss', function () {
    return view('profit-loss');
})->name('profit-loss');

Route::get('/project-analytics', function () {
    return view('project-analytics');
})->name('project-analytics');

Route::get('/project-dashboard', function () {
    return view('project-dashboard');
})->name('project-dashboard');

Route::get('/project-details', function () {
    return view('project-details');
})->name('project-details');

Route::get('/projects-grid', function () {
    return view('projects-grid');
})->name('projects-grid');

Route::get('/projects', function () {
    return view('projects');
})->name('projects');

Route::get('/purchase-details', function () {
    return view('purchase-details');
})->name('purchase-details');

Route::get('/purchase-order-details', function () {
    return view('purchase-order-details');
})->name('purchase-order-details');

Route::get('/purchase-orders', function () {
    return view('purchase-orders');
})->name('purchase-orders');

Route::get('/purchase-return-details', function () {
    return view('purchase-return-details');
})->name('purchase-return-details');

Route::get('/purchase-return', function () {
    return view('purchase-return');
})->name('purchase-return');

Route::get('/purchases', function () {
    return view('purchases');
})->name('purchases');

Route::get('/qr-code-print', function () {
    return view('qr-code-print');
})->name('qr-code-print');

Route::get('/quality-inspections', function () {
    return view('quality-inspections');
})->name('quality-inspections');

Route::get('/recruitment-analytics', function () {
    return view('recruitment-analytics');
})->name('recruitment-analytics');

Route::get('/recruitment', function () {
    return view('recruitment');
})->name('recruitment');

Route::get('/recurring-invoice-details', function () {
    return view('recurring-invoice-details');
})->name('recurring-invoice-details');

Route::get('/recurring-invoices', function () {
    return view('recurring-invoices');
})->name('recurring-invoices');

Route::get('/refunds', function () {
    return view('refunds');
})->name('refunds');

Route::get('/register-cover', function () {
    return view('register-cover');
})->name('register-cover');

Route::get('/register-illustration', function () {
    return view('register-illustration');
})->name('register-illustration');

Route::get('/reset-password-cover', function () {
    return view('reset-password-cover');
})->name('reset-password-cover');

Route::get('/reset-password-illustration', function () {
    return view('reset-password-illustration');
})->name('reset-password-illustration');

Route::get('/reset-password', function () {
    return view('reset-password');
})->name('reset-password');

Route::get('/resource-allocation', function () {
    return view('resource-allocation');
})->name('resource-allocation');

Route::get('/rfq-management', function () {
    return view('rfq-management');
})->name('rfq-management');

Route::get('/roles-permissions', function () {
    return view('roles-permissions');
})->name('roles-permissions');

Route::get('/sales-analytics', function () {
    return view('sales-analytics');
})->name('sales-analytics');

Route::get('/sales-dashboard', function () {
    return view('sales-dashboard');
})->name('sales-dashboard');

Route::get('/sales-details', function () {
    return view('sales-details');
})->name('sales-details');

Route::get('/sales-orders', function () {
    return view('sales-orders');
})->name('sales-orders');

Route::get('/sales-quotes-details', function () {
    return view('sales-quotes-details');
})->name('sales-quotes-details');

Route::get('/sales-quotes', function () {
    return view('sales-quotes');
})->name('sales-quotes');

Route::get('/security-settings', function () {
    return view('security-settings');
})->name('security-settings');

Route::get('/shipments', function () {
    return view('shipments');
})->name('shipments');

Route::get('/sla-management', function () {
    return view('sla-management');
})->name('sla-management');

Route::get('/sourcing-campaigns', function () {
    return view('sourcing-campaigns');
})->name('sourcing-campaigns');

Route::get('/stock-adjustment', function () {
    return view('stock-adjustment');
})->name('stock-adjustment');

Route::get('/stock-transfer', function () {
    return view('stock-transfer');
})->name('stock-transfer');

Route::get('/suppliers', function () {
    return view('suppliers');
})->name('suppliers');

Route::get('/support-dashboard', function () {
    return view('support-dashboard');
})->name('support-dashboard');

Route::get('/tables-basic', function () {
    return view('tables-basic');
})->name('tables-basic');

Route::get('/task-details', function () {
    return view('task-details');
})->name('task-details');

Route::get('/tasks-grid', function () {
    return view('tasks-grid');
})->name('tasks-grid');

Route::get('/tasks', function () {
    return view('tasks');
})->name('tasks');

Route::get('/tax-summary', function () {
    return view('tax-summary');
})->name('tax-summary');

Route::get('/taxes', function () {
    return view('taxes');
})->name('taxes');

Route::get('/ticket-details', function () {
    return view('ticket-details');
})->name('ticket-details');

Route::get('/tickets', function () {
    return view('tickets');
})->name('tickets');

Route::get('/timesheet', function () {
    return view('timesheet');
})->name('timesheet');

Route::get('/todo', function () {
    return view('todo');
})->name('todo');

Route::get('/training-development', function () {
    return view('training-development');
})->name('training-development');

Route::get('/transactions', function () {
    return view('transactions');
})->name('transactions');

Route::get('/ui-accordion', function () {
    return view('ui-accordion');
})->name('ui-accordion');

Route::get('/ui-alerts', function () {
    return view('ui-alerts');
})->name('ui-alerts');

Route::get('/ui-avatar', function () {
    return view('ui-avatar');
})->name('ui-avatar');

Route::get('/ui-badges', function () {
    return view('ui-badges');
})->name('ui-badges');

Route::get('/ui-breadcrumb', function () {
    return view('ui-breadcrumb');
})->name('ui-breadcrumb');

Route::get('/ui-buttons-group', function () {
    return view('ui-buttons-group');
})->name('ui-buttons-group');

Route::get('/ui-buttons', function () {
    return view('ui-buttons');
})->name('ui-buttons');

Route::get('/ui-cards', function () {
    return view('ui-cards');
})->name('ui-cards');

Route::get('/ui-clipboard', function () {
    return view('ui-clipboard');
})->name('ui-clipboard');

Route::get('/ui-collapse', function () {
    return view('ui-collapse');
})->name('ui-collapse');

Route::get('/ui-colors', function () {
    return view('ui-colors');
})->name('ui-colors');

Route::get('/ui-dragula', function () {
    return view('ui-dragula');
})->name('ui-dragula');

Route::get('/ui-dropdowns', function () {
    return view('ui-dropdowns');
})->name('ui-dropdowns');

Route::get('/ui-grid', function () {
    return view('ui-grid');
})->name('ui-grid');

Route::get('/ui-images', function () {
    return view('ui-images');
})->name('ui-images');

Route::get('/ui-lightbox', function () {
    return view('ui-lightbox');
})->name('ui-lightbox');

Route::get('/ui-modals', function () {
    return view('ui-modals');
})->name('ui-modals');

Route::get('/ui-nav-tabs', function () {
    return view('ui-nav-tabs');
})->name('ui-nav-tabs');

Route::get('/ui-offcanvas', function () {
    return view('ui-offcanvas');
})->name('ui-offcanvas');

Route::get('/ui-pagination', function () {
    return view('ui-pagination');
})->name('ui-pagination');

Route::get('/ui-popovers', function () {
    return view('ui-popovers');
})->name('ui-popovers');

Route::get('/ui-progress', function () {
    return view('ui-progress');
})->name('ui-progress');

Route::get('/ui-rangeslider', function () {
    return view('ui-rangeslider');
})->name('ui-rangeslider');

Route::get('/ui-typography', function () {
    return view('ui-typography');
})->name('ui-typography');

Route::get('/units', function () {
    return view('units');
})->name('units');

Route::get('/user-management', function () {
    return view('user-management');
})->name('user-management');

Route::get('/master/users', [UserController::class, 'index'])->name('master-users.index');
Route::post('/master/users', [UserController::class, 'store'])->name('master-users.store');
Route::get('/master/users/{user}/edit', [UserController::class, 'edit'])->name('master-users.edit');
Route::put('/master/users/{user}', [UserController::class, 'update'])->name('master-users.update');
Route::delete('/master/users/{user}', [UserController::class, 'destroy'])->name('master-users.destroy');

Route::get('/vendors', function () {
    return view('vendors');
})->name('vendors');

Route::get('/video-call', function () {
    return view('video-call');
})->name('video-call');

Route::get('/voice-call', function () {
    return view('voice-call');
})->name('voice-call');

Route::get('/warehouse', function () {
    return view('warehouse');
})->name('warehouse');

Route::get('/work-orders', function () {
    return view('work-orders');
})->name('work-orders');

Route::get('/workflow-actions', function () {
    return view('workflow-actions');
})->name('workflow-actions');

Route::get('/workflow-approval-levels', function () {
    return view('workflow-approval-levels');
})->name('workflow-approval-levels');

Route::get('/workflow-approvals', function () {
    return view('workflow-approvals');
})->name('workflow-approvals');

Route::get('/workflow-requests', function () {
    return view('workflow-requests');
})->name('workflow-requests');

});
