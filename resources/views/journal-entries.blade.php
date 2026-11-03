@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">

        <!-- Page Header -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 lg:mb-6">
            <div>
                <h1 class="text-gray-900 text-xl font-bold mb-1">Journal Entries</h1>
                <p class="text-xs text-default mb-0">Record and review double-entry bookkeeping vouchers and general ledger adjustments</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-light cursor-pointer">
                    <i class="ph-duotone ph-printer"></i> Print
                </button>
                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-primary hover:border-primary hover:text-white focus:bg-primary focus:border-primary focus:text-white focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                        <i class="icon-download font-normal"></i> Export <i class="icon-chevron-down"></i>
                    </button>
                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                        <div class="p-2 space-y-1">
                            <a class="flex items-center px-2 py-1.5 rounded-md text-xs text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">
                                Export as PDF
                            </a>
                            <a class="flex items-center px-2 py-1.5 rounded-md text-xs text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">
                                Export as Excel
                            </a>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-sm bg-dark text-white border border-dark inline-flex items-center gap-2 hover:bg-primary-hover hover:border-primary-hover cursor-pointer" data-hs-overlay="#add-journal-modal">
                    <i class="ph ph-plus"></i> Post Journal Entry
                </button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Total Journal Entries</p>
                        <h2 class="text-xl font-bold text-gray-900 mb-0">1,482</h2>
                    </div>
                    <div class="size-9 rounded-md bg-primary-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-book-open text-primary text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0 flex items-center gap-1">
                    <span class="text-success inline-flex items-center"><i class="ph ph-trend-up"></i> +42</span> recorded this month
                </p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Unposted / Drafts</p>
                        <h2 class="text-xl font-bold text-gray-900 mb-0">18</h2>
                    </div>
                    <div class="size-9 rounded-md bg-warning-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-clock text-warning text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0 flex items-center gap-1">
                    <span class="text-warning font-medium">Action Required</span> pending manager sign-off
                </p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Total Debits</p>
                        <h2 class="text-xl font-bold text-gray-900 mb-0">$3,842,500</h2>
                    </div>
                    <div class="size-9 rounded-md bg-info-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-arrow-up-right text-info text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0 flex items-center gap-1">
                    <span class="text-info font-medium">Fiscal YTD</span> ledger debits
                </p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Total Credits</p>
                        <h2 class="text-xl font-bold text-gray-900 mb-0">$3,842,500</h2>
                    </div>
                    <div class="size-9 rounded-md bg-success-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-scales text-success text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0 flex items-center gap-1">
                    <span class="text-success inline-flex items-center"><i class="ph ph-check-circle"></i> 100% Balanced</span> no variance
                </p>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="bg-white border border-border-color rounded-md p-4 pb-0">

            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="relative w-64 search-input">
                        <i class="ph ph-magnifying-glass absolute right-2.5 top-1/2 -translate-y-1/2 text-default text-sm"></i>
                        <input type="text" class="w-full px-3 pe-8 py-2 h-7 text-[12px]! border border-border-color rounded-md bg-white focus:outline-none focus:ring-0" placeholder="Search voucher # or note...">
                    </div>
                    <div class="relative rangepicker-input w-[174px] h-[28px] leading-none">
                        <span class="absolute inset-y-0 left-0 flex items-center px-3 text-muted-foreground text-dark text-xs! pointer-events-none">
                            <i class="icon-calendar"></i>
                        </span>
                        <input type="text" class="form-input text-xs! h-[28px] inline-block w-full bg-light border-border-color rounded-md focus:ring-0 disabled:opacity-50 disabled:pointer-events-none focus:outline-none focus:border-border-color pl-8! ps-8!" data-provider="flatpickr" data-date-format="d M y" data-range-date="true" value="01 Jan 26 to 20 Jan 26" id="picker">
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="hs-dropdown [--placement:bottom-left] [--auto-close:inside] relative inline-flex">
                        <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-primary hover:border-primary hover:text-white focus:bg-primary focus:border-primary focus:text-white focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            <i class="icon-funnel font-normal"></i> Status <i class="icon-chevron-down"></i>
                        </button>
                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                            <div class="p-2 space-y-1">
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Posted</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Draft</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0"> Reversed</label>
                                <div class="flex items-center justify-between gap-2 mt-3">
                                    <button type="button" class="btn-sm bg-white w-full border border-border-color text-gray-900 flex items-center justify-center hover:bg-light cursor-pointer">Reset</button>
                                    <button type="button" class="btn-sm bg-dark w-full border border-dark text-white flex items-center justify-center hover:bg-primary hover:border-primary hover:text-white cursor-pointer">Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                        <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-primary hover:border-primary hover:text-white focus:bg-primary focus:border-primary focus:text-white focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            <i class="icon-arrow-up-down font-normal"></i> Sort By <i class="icon-chevron-down"></i>
                        </button>
                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                            <div class="p-2 space-y-1">
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Newest First</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Oldest First</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Amount: High to Low</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-sm text-default border-b border-border-color">
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Voucher #</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Date</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Reference / Narration</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Debit Account</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Credit Account</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Amount</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Created By</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Status</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#JV-2026-0091</td>
                            <td class="py-2.5 px-2 text-default">20 Jan 2026</td>
                            <td class="py-2.5 px-2 font-medium text-title">Monthly Office Lease Depreciation Amortization</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">5200</span> - Rent Expense</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">1400</span> - Prepaid Lease Asset</td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">$18,500.00</td>
                            <td class="py-2.5 px-2 text-default">David Ross (CPA)</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded">Posted</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#view-journal-modal"><i class="icon-eye"></i> View Voucher</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-danger hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#delete-modal"><i class="ph ph-arrow-counter-clockwise"></i> Reverse Entry</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#JV-2026-0090</td>
                            <td class="py-2.5 px-2 text-default">18 Jan 2026</td>
                            <td class="py-2.5 px-2 font-medium text-title">Bi-weekly Staff Payroll Accrual Allocation</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">5100</span> - Salary & Wages</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">2200</span> - Accrued Payroll</td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">$142,350.00</td>
                            <td class="py-2.5 px-2 text-default">Sophia Martinez</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded">Posted</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#view-journal-modal"><i class="icon-eye"></i> View Voucher</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-danger hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#delete-modal"><i class="ph ph-arrow-counter-clockwise"></i> Reverse Entry</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#JV-2026-0089</td>
                            <td class="py-2.5 px-2 text-default">17 Jan 2026</td>
                            <td class="py-2.5 px-2 font-medium text-title">Machinery Depreciation Month 1 Allocation</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">5400</span> - Depreciation Exp</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">1590</span> - Acc. Depreciation</td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">$8,750.00</td>
                            <td class="py-2.5 px-2 text-default">David Ross (CPA)</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded">Posted</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#view-journal-modal"><i class="icon-eye"></i> View Voucher</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-danger hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#delete-modal"><i class="ph ph-arrow-counter-clockwise"></i> Reverse Entry</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#JV-2026-0088</td>
                            <td class="py-2.5 px-2 text-default">15 Jan 2026</td>
                            <td class="py-2.5 px-2 font-medium text-title">Inter-Bank Funds Transfer (Operating to Payroll)</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">1015</span> - Payroll Bank Acct</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">1010</span> - Main Chase Acct</td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">$150,000.00</td>
                            <td class="py-2.5 px-2 text-default">Alex Rivera</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded">Draft</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#view-journal-modal"><i class="icon-eye"></i> View Voucher</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-success hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-check-circle"></i> Post to Ledger</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#JV-2026-0087</td>
                            <td class="py-2.5 px-2 text-default">12 Jan 2026</td>
                            <td class="py-2.5 px-2 font-medium text-title">Quarterly Insurance Prepayment Amortization</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">5350</span> - Insurance Exp</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">1410</span> - Prepaid Insurance</td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">$4,500.00</td>
                            <td class="py-2.5 px-2 text-default">David Ross (CPA)</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded">Posted</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#view-journal-modal"><i class="icon-eye"></i> View Voucher</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-danger hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#delete-modal"><i class="ph ph-arrow-counter-clockwise"></i> Reverse Entry</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#JV-2026-0086</td>
                            <td class="py-2.5 px-2 text-default">10 Jan 2026</td>
                            <td class="py-2.5 px-2 font-medium text-title">Petty Cash Month-Start Replenishment</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">1020</span> - Petty Cash</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">1010</span> - Operating Bank</td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">$2,000.00</td>
                            <td class="py-2.5 px-2 text-default">Alex Rivera</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded">Posted</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#view-journal-modal"><i class="icon-eye"></i> View Voucher</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-danger hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#delete-modal"><i class="ph ph-arrow-counter-clockwise"></i> Reverse Entry</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#JV-2026-0085</td>
                            <td class="py-2.5 px-2 text-default">08 Jan 2026</td>
                            <td class="py-2.5 px-2 font-medium text-title">Bad Debt Write-off Adjustment (Old Invoices)</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">5900</span> - Bad Debt Exp</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">1200</span> - Accts Receivable</td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">$6,200.00</td>
                            <td class="py-2.5 px-2 text-default">David Ross (CPA)</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-danger-transparent text-danger px-2 py-0.5 rounded">Reversed</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#view-journal-modal"><i class="icon-eye"></i> View Audit Log</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#JV-2026-0084</td>
                            <td class="py-2.5 px-2 text-default">05 Jan 2026</td>
                            <td class="py-2.5 px-2 font-medium text-title">Annual Software Licenses Cost Deferred Transfer</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">1420</span> - Prepaid Software</td>
                            <td class="py-2.5 px-2 text-default"><span class="font-medium text-gray-900">2010</span> - Accounts Payable</td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">$36,000.00</td>
                            <td class="py-2.5 px-2 text-default">Alex Rivera</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded">Posted</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#view-journal-modal"><i class="icon-eye"></i> View Voucher</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-danger hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#delete-modal"><i class="ph ph-arrow-counter-clockwise"></i> Reverse Entry</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 pb-3 border-t border-border-color">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-default">Showing</span>
                    <select class="px-2 py-1 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0 w-28">
                        <option>10 / Pages</option>
                        <option>25 / Pages</option>
                        <option>50 / Pages</option>
                    </select>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" class="size-7 rounded-md text-sm bg-dark text-white flex items-center justify-center cursor-pointer">1</button>
                    <button type="button" class="size-7 rounded-md text-sm text-gray-900 hover:bg-light flex items-center justify-center cursor-pointer">2</button>
                    <button type="button" class="size-7 rounded-md text-sm text-gray-900 hover:bg-light flex items-center justify-center cursor-pointer">3</button>
                    <button type="button" class="size-7 rounded-md text-sm border border-border-color flex items-center justify-center cursor-pointer text-default hover:bg-light ml-1" aria-label="Previous"><i class="ph ph-caret-left text-sm"></i></button>
                    <button type="button" class="size-7 rounded-md text-sm border border-border-color flex items-center justify-center cursor-pointer text-default hover:bg-light" aria-label="Next"><i class="ph ph-caret-right text-sm"></i></button>
                </div>
            </div>

        </div>

    </div>
    <!-- End Content -->

@endsection
