@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">

        <!-- Page Header -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 lg:mb-6">
            <div>
                <h1 class="text-gray-900 text-xl font-bold mb-1">Bank Reconciliation &amp; Ledger Matching</h1>
                <p class="text-xs text-default mb-0">Match bank statement transactions against General Ledger cash accounts, audit cleared items, and reconcile balances</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-light cursor-pointer">
                    <i class="ph-duotone ph-printer"></i> Reconciliation Summary
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
                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-light cursor-pointer" data-hs-overlay="#import-statement-modal">
                    <i class="ph ph-upload-simple"></i> Import Statement
                </button>
                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-light cursor-pointer" data-hs-overlay="#post-adjustment-modal">
                    <i class="ph ph-plus-circle"></i> Post Adjustment
                </button>
                <button type="button" class="btn-sm bg-dark text-white border border-dark inline-flex items-center gap-2 hover:bg-primary-hover hover:border-primary-hover cursor-pointer">
                    <i class="ph ph-check-circle"></i> Lock &amp; Reconcile
                </button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Statement Ending Balance</p>
                        <h2 class="text-xl font-bold text-gray-900 mb-0">$542,890.50</h2>
                    </div>
                    <div class="size-9 rounded-md bg-primary-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-bank text-primary text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0 flex items-center gap-1">
                    <span class="text-primary font-medium">Chase Operating (*4821)</span> feed
                </p>
            </div>

            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">General Ledger Book Balance</p>
                        <h2 class="text-xl font-bold text-gray-900 mb-0">$542,890.50</h2>
                    </div>
                    <div class="size-9 rounded-md bg-info-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-book-open text-info text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0 flex items-center gap-1">
                    <span class="text-info font-medium">GL Acct #1010-00</span> cash book
                </p>
            </div>

            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Cleared Transactions</p>
                        <h2 class="text-xl font-bold text-success mb-0">$184,210.00</h2>
                    </div>
                    <div class="size-9 rounded-md bg-success-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-arrows-clockwise text-success text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-success mb-0 font-medium flex items-center gap-1">
                    <span>48 matched items</span> (96% cleared rate)
                </p>
            </div>

            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Unreconciled Difference</p>
                        <h2 class="text-xl font-bold text-success mb-0">$0.00 Delta</h2>
                    </div>
                    <div class="size-9 rounded-md bg-success-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-check-circle text-success text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-success mb-0 font-medium flex items-center gap-1">
                    <span>Perfect balance</span> - Ready to certify
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
                        <input type="text" class="w-full px-3 pe-8 py-2 h-7 text-[12px]! border border-border-color rounded-md bg-white focus:outline-none focus:ring-0" placeholder="Search payee, check #, or memo...">
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
                            <i class="ph ph-bank font-normal"></i> Account <i class="icon-chevron-down"></i>
                        </button>
                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-56 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                            <div class="p-2 space-y-1">
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 font-semibold bg-light hover:text-primary" href="#">Chase Operating (*4821)</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-primary" href="#">Silicon Valley Reserve (*1092)</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-primary" href="#">Citi Treasury Payroll (*3310)</a>
                            </div>
                        </div>
                    </div>
                    <div class="hs-dropdown [--placement:bottom-left] [--auto-close:inside] relative inline-flex">
                        <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-primary hover:border-primary hover:text-white focus:bg-primary focus:border-primary focus:text-white focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            <i class="icon-funnel font-normal"></i> Status <i class="icon-chevron-down"></i>
                        </button>
                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                            <div class="p-2 space-y-1">
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Matched &amp; Cleared</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Uncleared GL Items</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Unmatched Bank Feed</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0"> Fee Adjustments</label>
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
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Date: Most Recent</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Amount: High to Low</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Status: Unmatched First</a>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-sm bg-primary text-white border border-primary hover:bg-primary-hover hover:border-primary-hover cursor-pointer inline-flex items-center gap-1.5">
                        <i class="ph ph-lightning"></i> Auto-Match
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-sm text-default border-b border-border-color">
                            <th class="text-left py-2 px-2 font-semibold text-gray-900 w-10">
                                <input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0">
                            </th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Cleared Date</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Bank Feed Description &amp; Ref</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">GL Account &amp; Voucher</th>
                            <th class="text-right py-2 px-2 font-semibold text-gray-900">Bank Amount</th>
                            <th class="text-right py-2 px-2 font-semibold text-gray-900">GL Amount</th>
                            <th class="text-right py-2 px-2 font-semibold text-gray-900">Variance</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Match Status</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">26 Sep 2026</span>
                                <span class="text-[11px] text-default">Wire Incoming</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">Apex Industrial Corp - Wire In</span>
                                <span class="text-[11px] text-default">REF: WIRE-88190-APEX</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">1010-00 / JV-2026-0418</span>
                                <span class="text-[11px] text-default">Inv #9910 Apex Computer</span>
                            </td>
                            <td class="py-2.5 px-2 text-right font-bold text-success">+$45,200.00</td>
                            <td class="py-2.5 px-2 text-right font-bold text-success">+$45,200.00</td>
                            <td class="py-2.5 px-2 text-right font-mono text-default">$0.00</td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium inline-flex items-center gap-1">
                                    <i class="ph ph-check-circle"></i> Matched
                                </span>
                            </td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-file-text"></i> View Journal Voucher</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-arrows-clockwise"></i> Re-verify Trace</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">25 Sep 2026</span>
                                <span class="text-[11px] text-default">ACH Outgoing</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">ACH Out: Global Logistics Ltd</span>
                                <span class="text-[11px] text-default">ACH-TRACE-77189</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">1010-00 / JV-2026-0414</span>
                                <span class="text-[11px] text-default">Freight Bill Sep Remittance</span>
                            </td>
                            <td class="py-2.5 px-2 text-right font-bold text-danger">-$8,450.00</td>
                            <td class="py-2.5 px-2 text-right font-bold text-danger">-$8,450.00</td>
                            <td class="py-2.5 px-2 text-right font-mono text-default">$0.00</td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium inline-flex items-center gap-1">
                                    <i class="ph ph-check-circle"></i> Matched
                                </span>
                            </td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-file-text"></i> View Journal Voucher</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">24 Sep 2026</span>
                                <span class="text-[11px] text-default">Direct Deposit</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">Vortex Dynamics - Remittance</span>
                                <span class="text-[11px] text-default">INV-2026-0811</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">1010-00 / JV-2026-0410</span>
                                <span class="text-[11px] text-default">Customer Settlement Acct</span>
                            </td>
                            <td class="py-2.5 px-2 text-right font-bold text-success">+$31,800.00</td>
                            <td class="py-2.5 px-2 text-right font-bold text-success">+$31,800.00</td>
                            <td class="py-2.5 px-2 text-right font-mono text-default">$0.00</td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium inline-flex items-center gap-1">
                                    <i class="ph ph-check-circle"></i> Matched
                                </span>
                            </td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-file-text"></i> View Journal Voucher</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0">
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">23 Sep 2026</span>
                                <span class="text-[11px] text-default">Bank Service Fee</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">Wire Service Fee - Chase Foreign</span>
                                <span class="text-[11px] text-default">CHASE-FEE-INTL-90</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-500 text-sm block italic">Unposted GL Entry</span>
                                <span class="text-[11px] text-warning font-medium">Pending 6140 Fee Voucher</span>
                            </td>
                            <td class="py-2.5 px-2 text-right font-bold text-danger">-$45.00</td>
                            <td class="py-2.5 px-2 text-right text-default">-</td>
                            <td class="py-2.5 px-2 text-right font-mono text-danger font-semibold">-$45.00</td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-medium inline-flex items-center gap-1">
                                    <i class="ph ph-warning"></i> Adjustment Req
                                </span>
                            </td>
                            <td class="py-2.5 px-2">
                                <button type="button" class="btn-xs bg-primary text-white rounded px-2 py-1 text-xs hover:bg-primary-hover cursor-pointer inline-flex items-center gap-1" data-hs-overlay="#post-adjustment-modal">
                                    <i class="ph ph-plus"></i> Adjust
                                </button>
                            </td>
                        </tr>

                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">22 Sep 2026</span>
                                <span class="text-[11px] text-default">Check Clearance</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">Check #4409 Cleared: FastFreight</span>
                                <span class="text-[11px] text-default">CHK-4409</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">1010-00 / JV-2026-0401</span>
                                <span class="text-[11px] text-default">A/P Supplier Check #4409</span>
                            </td>
                            <td class="py-2.5 px-2 text-right font-bold text-danger">-$4,200.00</td>
                            <td class="py-2.5 px-2 text-right font-bold text-danger">-$4,200.00</td>
                            <td class="py-2.5 px-2 text-right font-mono text-default">$0.00</td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium inline-flex items-center gap-1">
                                    <i class="ph ph-check-circle"></i> Matched
                                </span>
                            </td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-file-text"></i> View Journal Voucher</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">21 Sep 2026</span>
                                <span class="text-[11px] text-default">Customer ACH</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">Beats Soundware - Settlement</span>
                                <span class="text-[11px] text-default">ACH-889102-BTS</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">1010-00 / JV-2026-0394</span>
                                <span class="text-[11px] text-default">A/R Customer Settlement</span>
                            </td>
                            <td class="py-2.5 px-2 text-right font-bold text-success">+$15,000.00</td>
                            <td class="py-2.5 px-2 text-right font-bold text-success">+$15,000.00</td>
                            <td class="py-2.5 px-2 text-right font-mono text-default">$0.00</td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium inline-flex items-center gap-1">
                                    <i class="ph ph-check-circle"></i> Matched
                                </span>
                            </td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-file-text"></i> View Journal Voucher</a>
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

        <!-- Transit Items Summary Card -->
        <div class="bg-white border border-border-color rounded-md p-4 mt-4">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 mb-0.5">Outstanding In-Transit Checks &amp; Uncleared Deposits</h3>
                    <p class="text-xs text-default mb-0">Transactions recorded in ERP general ledger not yet cleared on bank statement</p>
                </div>
                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-1.5 hover:bg-light text-xs cursor-pointer">
                    <i class="ph ph-arrows-clockwise"></i> Re-check Bank Clearing
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-3 border border-border-color rounded-md bg-light/50">
                    <p class="text-xs text-default mb-1">Uncleared Vendor Checks (A/P)</p>
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-gray-900 text-sm">$12,450.00</span>
                        <span class="text-[11px] bg-info-transparent text-info px-2 py-0.5 rounded font-medium">3 Checks</span>
                    </div>
                </div>
                <div class="p-3 border border-border-color rounded-md bg-light/50">
                    <p class="text-xs text-default mb-1">Deposits in Transit (A/R)</p>
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-gray-900 text-sm">$18,200.00</span>
                        <span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">2 Deposits</span>
                    </div>
                </div>
                <div class="p-3 border border-border-color rounded-md bg-light/50">
                    <p class="text-xs text-default mb-1">Net In-Transit Variance</p>
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-primary text-sm">+$5,750.00</span>
                        <span class="text-default text-[11px]">Auto-clears next cycle</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- End Content -->

@endsection
