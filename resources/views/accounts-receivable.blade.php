@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">

        <!-- Page Header -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 lg:mb-6">
            <div>
                <h1 class="text-gray-900 text-xl font-bold mb-1">Accounts Receivable (Aging Report)</h1>
                <p class="text-xs text-default mb-0">Track customer invoice payment aging, overdue collections, and credit risk profiles</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-light cursor-pointer">
                    <i class="ph-duotone ph-printer"></i> Print Report
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
                <button type="button" class="btn-sm bg-dark text-white border border-dark inline-flex items-center gap-2 hover:bg-primary-hover hover:border-primary-hover cursor-pointer" data-hs-overlay="#record-payment-modal">
                    <i class="ph ph-hand-coins"></i> Record Payment
                </button>
            </div>
        </div>

        <!-- Aging KPI Metric Cards (5 Buckets) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-4">
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Total Receivables</p>
                        <h2 class="text-lg font-bold text-gray-900 mb-0">$342,800</h2>
                    </div>
                    <div class="size-8 rounded-md bg-primary-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-wallet text-primary text-base"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0">Across 42 customers</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Current (0–30 Days)</p>
                        <h2 class="text-lg font-bold text-success mb-0">$185,400</h2>
                    </div>
                    <div class="size-8 rounded-md bg-success-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-check text-success text-base"></i>
                    </div>
                </div>
                <p class="text-[11px] text-success mb-0">54.1% in good standing</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">31–60 Days</p>
                        <h2 class="text-lg font-bold text-info mb-0">$84,200</h2>
                    </div>
                    <div class="size-8 rounded-md bg-info-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-calendar text-info text-base"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0">Reminders sent</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">61–90 Days</p>
                        <h2 class="text-lg font-bold text-warning mb-0">$48,600</h2>
                    </div>
                    <div class="size-8 rounded-md bg-warning-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-warning text-warning text-base"></i>
                    </div>
                </div>
                <p class="text-[11px] text-warning mb-0">Follow-up required</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Over 90 Days</p>
                        <h2 class="text-lg font-bold text-danger mb-0">$24,600</h2>
                    </div>
                    <div class="size-8 rounded-md bg-danger-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-shield-warning text-danger text-base"></i>
                    </div>
                </div>
                <p class="text-[11px] text-danger mb-0 font-medium">Critical overdue risk</p>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="bg-white border border-border-color rounded-md p-4 pb-0">

            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="relative w-64 search-input">
                        <i class="ph ph-magnifying-glass absolute right-2.5 top-1/2 -translate-y-1/2 text-default text-sm"></i>
                        <input type="text" class="w-full px-3 pe-8 py-2 h-7 text-[12px]! border border-border-color rounded-md bg-white focus:outline-none focus:ring-0" placeholder="Search customer or company...">
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
                            <i class="icon-funnel font-normal"></i> Risk Level <i class="icon-chevron-down"></i>
                        </button>
                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                            <div class="p-2 space-y-1">
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Low Risk</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Moderate Risk</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> High Risk</label>
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
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Overdue: High to Low</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Total Balance: High to Low</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Customer Name: A - Z</a>
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
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Customer</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Total Due</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Current (0-30d)</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">31-60 Days</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">61-90 Days</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">90+ Days</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Last Payment</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Credit Risk</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-8 rounded-full bg-primary-transparent text-primary flex items-center justify-center font-bold text-xs shrink-0">AC</div>
                                    <div>
                                        <a href="{{url('contact-details')}}" class="font-semibold text-title text-sm block hover:text-primary">Apex Computers Corp</a>
                                        <span class="text-[11px] text-default">apex@example.com</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$64,500.00</td>
                            <td class="py-2.5 px-2 text-default">$40,000.00</td>
                            <td class="py-2.5 px-2 text-default">$24,500.00</td>
                            <td class="py-2.5 px-2 text-default">$0.00</td>
                            <td class="py-2.5 px-2 text-default">$0.00</td>
                            <td class="py-2.5 px-2 text-default">14 Jan 2026</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Low Risk</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#record-payment-modal"><i class="ph ph-hand-coins"></i> Record Payment</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#reminder-modal"><i class="ph ph-bell"></i> Send Reminder</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="{{url('recurring-invoices')}}"><i class="icon-eye"></i> View Invoices</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-8 rounded-full bg-info-transparent text-info flex items-center justify-center font-bold text-xs shrink-0">BH</div>
                                    <div>
                                        <a href="{{url('contact-details')}}" class="font-semibold text-title text-sm block hover:text-primary">Beats Soundware Ltd</a>
                                        <span class="text-[11px] text-default">beats@example.com</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$45,200.00</td>
                            <td class="py-2.5 px-2 text-default">$20,200.00</td>
                            <td class="py-2.5 px-2 text-default">$15,000.00</td>
                            <td class="py-2.5 px-2 text-warning font-semibold">$10,000.00</td>
                            <td class="py-2.5 px-2 text-default">$0.00</td>
                            <td class="py-2.5 px-2 text-default">28 Dec 2025</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-medium">Moderate</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#record-payment-modal"><i class="ph ph-hand-coins"></i> Record Payment</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#reminder-modal"><i class="ph ph-bell"></i> Send Reminder</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-8 rounded-full bg-danger-transparent text-danger flex items-center justify-center font-bold text-xs shrink-0">GL</div>
                                    <div>
                                        <a href="{{url('contact-details')}}" class="font-semibold text-title text-sm block hover:text-primary">Global Logistics Group</a>
                                        <span class="text-[11px] text-default">finance@globallog.com</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$38,800.00</td>
                            <td class="py-2.5 px-2 text-default">$0.00</td>
                            <td class="py-2.5 px-2 text-default">$8,200.00</td>
                            <td class="py-2.5 px-2 text-warning font-semibold">$12,000.00</td>
                            <td class="py-2.5 px-2 text-danger font-bold">$18,600.00</td>
                            <td class="py-2.5 px-2 text-default">12 Nov 2025</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-danger-transparent text-danger px-2 py-0.5 rounded font-medium">Critical</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#record-payment-modal"><i class="ph ph-hand-coins"></i> Record Payment</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-danger hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#reminder-modal"><i class="ph ph-warning-circle"></i> Final Notice</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-8 rounded-full bg-purple-transparent text-purple flex items-center justify-center font-bold text-xs shrink-0">NT</div>
                                    <div>
                                        <a href="{{url('contact-details')}}" class="font-semibold text-title text-sm block hover:text-primary">NextGen Technologies</a>
                                        <span class="text-[11px] text-default">billing@nextgen.io</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$52,100.00</td>
                            <td class="py-2.5 px-2 text-default">$38,000.00</td>
                            <td class="py-2.5 px-2 text-default">$14,100.00</td>
                            <td class="py-2.5 px-2 text-default">$0.00</td>
                            <td class="py-2.5 px-2 text-default">$0.00</td>
                            <td class="py-2.5 px-2 text-default">15 Jan 2026</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Low Risk</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#record-payment-modal"><i class="ph ph-hand-coins"></i> Record Payment</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#reminder-modal"><i class="ph ph-bell"></i> Send Reminder</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-8 rounded-full bg-orange-transparent text-orange flex items-center justify-center font-bold text-xs shrink-0">ST</div>
                                    <div>
                                        <a href="{{url('contact-details')}}" class="font-semibold text-title text-sm block hover:text-primary">Starlight Retailers Inc</a>
                                        <span class="text-[11px] text-default">starlight@example.com</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$29,400.00</td>
                            <td class="py-2.5 px-2 text-default">$12,000.00</td>
                            <td class="py-2.5 px-2 text-default">$11,400.00</td>
                            <td class="py-2.5 px-2 text-warning font-semibold">$6,000.00</td>
                            <td class="py-2.5 px-2 text-default">$0.00</td>
                            <td class="py-2.5 px-2 text-default">04 Jan 2026</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-medium">Moderate</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#record-payment-modal"><i class="ph ph-hand-coins"></i> Record Payment</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#reminder-modal"><i class="ph ph-bell"></i> Send Reminder</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-8 rounded-full bg-cyan-transparent text-cyan flex items-center justify-center font-bold text-xs shrink-0">VC</div>
                                    <div>
                                        <a href="{{url('contact-details')}}" class="font-semibold text-title text-sm block hover:text-primary">Vanguard Capital Partners</a>
                                        <span class="text-[11px] text-default">ops@vanguardcap.com</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$72,800.00</td>
                            <td class="py-2.5 px-2 text-default">$45,000.00</td>
                            <td class="py-2.5 px-2 text-default">$21,800.00</td>
                            <td class="py-2.5 px-2 text-default">$0.00</td>
                            <td class="py-2.5 px-2 text-danger font-bold">$6,000.00</td>
                            <td class="py-2.5 px-2 text-default">19 Jan 2026</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-medium">Moderate</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#record-payment-modal"><i class="ph ph-hand-coins"></i> Record Payment</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#reminder-modal"><i class="ph ph-bell"></i> Send Reminder</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-8 rounded-full bg-teal-transparent text-teal flex items-center justify-center font-bold text-xs shrink-0">ZS</div>
                                    <div>
                                        <a href="{{url('contact-details')}}" class="font-semibold text-title text-sm block hover:text-primary">Zenith Software Solutions</a>
                                        <span class="text-[11px] text-default">accounts@zenithsoft.com</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$30,000.00</td>
                            <td class="py-2.5 px-2 text-default">$30,000.00</td>
                            <td class="py-2.5 px-2 text-default">$0.00</td>
                            <td class="py-2.5 px-2 text-default">$0.00</td>
                            <td class="py-2.5 px-2 text-default">$0.00</td>
                            <td class="py-2.5 px-2 text-default">18 Jan 2026</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Low Risk</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#record-payment-modal"><i class="ph ph-hand-coins"></i> Record Payment</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#reminder-modal"><i class="ph ph-bell"></i> Send Reminder</a>
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
