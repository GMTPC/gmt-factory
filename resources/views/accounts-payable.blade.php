@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">

        <!-- Page Header -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h1 class="text-gray-900 text-xl font-bold mb-1">Accounts Payable (Vendor Aging)</h1>
                <p class="text-xs text-default mb-0">Track outgoing supplier liabilities, cash commitments, and 3-way invoice matching.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-light cursor-pointer">
                    <i class="ph-duotone ph-printer"></i> Print Aging
                </button>
                <div class="hs-dropdown [--placement:bottom-right] relative inline-flex">
                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-light" aria-haspopup="menu" aria-expanded="false" aria-label="Export">
                        <i class="icon-download font-normal"></i> Export <i class="icon-chevron-down"></i>
                    </button>
                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-40 bg-white border border-border-color shadow-sm rounded-md mt-2 z-10" role="menu">
                        <div class="p-1">
                            <a class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light" href="#"><i class="ph ph-file-pdf text-danger"></i> Export as PDF</a>
                            <a class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light" href="#"><i class="ph ph-file-xls text-success"></i> Export as Excel</a>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-sm bg-dark text-white border border-dark inline-flex items-center gap-2 hover:bg-primary-hover hover:border-primary-hover cursor-pointer" data-hs-overlay="#record-payment-modal">
                    <i class="ph ph-credit-card"></i> Pay Vendor Bill
                </button>
            </div>
        </div>

        <!-- Aging Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-4">
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Total Payables</p>
                        <h2 class="text-lg font-bold text-gray-900 mb-0">$284,500</h2>
                    </div>
                    <div class="size-8 rounded-md bg-primary-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-receipt text-primary text-base"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0">Across 28 verified vendors</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Current (0–30 Days)</p>
                        <h2 class="text-lg font-bold text-success mb-0">$162,100</h2>
                    </div>
                    <div class="size-8 rounded-md bg-success-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-check-circle text-success text-base"></i>
                    </div>
                </div>
                <p class="text-[11px] text-success mb-0">Within terms (57.0%)</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">31–60 Days</p>
                        <h2 class="text-lg font-bold text-info mb-0">$74,800</h2>
                    </div>
                    <div class="size-8 rounded-md bg-info-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-calendar text-info text-base"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0">Approved for release</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">61–90 Days</p>
                        <h2 class="text-lg font-bold text-warning mb-0">$32,400</h2>
                    </div>
                    <div class="size-8 rounded-md bg-warning-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-warning text-warning text-base"></i>
                    </div>
                </div>
                <p class="text-[11px] text-warning mb-0">Early payment discount missed</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Over 90 Days</p>
                        <h2 class="text-lg font-bold text-danger mb-0">$15,200</h2>
                    </div>
                    <div class="size-8 rounded-md bg-danger-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-shield-warning text-danger text-base"></i>
                    </div>
                </div>
                <p class="text-[11px] text-danger mb-0">Under dispute / inspection hold</p>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="bg-white border border-border-color rounded-md p-4 pb-0">

            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="relative w-64 search-input">
                        <i class="ph ph-magnifying-glass absolute right-2.5 top-1/2 -translate-y-1/2 text-default text-sm"></i>
                        <input type="text" class="w-full px-3 pe-8 py-2 h-7 text-[12px]! border border-border-color rounded-md bg-white focus:outline-none focus:ring-0" placeholder="Search vendor, bill #, or PO...">
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
                            <i class="icon-funnel font-normal"></i> 3-Way Match <i class="icon-chevron-down"></i>
                        </button>
                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-44 bg-white border border-border-color shadow rounded-md mt-2 z-10" role="menu">
                            <div class="p-2 space-y-1">
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light"><input type="checkbox" class="size-4 rounded border-border-color text-primary" checked> Matched (PO=GR=Bill)</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light"><input type="checkbox" class="size-4 rounded border-border-color text-primary" checked> Pending Receipt</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light"><input type="checkbox" class="size-4 rounded border-border-color text-primary"> Price Variance</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light"><input type="checkbox" class="size-4 rounded border-border-color text-primary"> Quality Hold</label>
                            </div>
                        </div>
                    </div>
                    <div class="hs-dropdown [--placement:bottom-left] [--auto-close:inside] relative inline-flex">
                        <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-primary hover:border-primary hover:text-white focus:bg-primary focus:border-primary focus:text-white focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            <i class="icon-arrow-up-down font-normal"></i> Sort By <i class="icon-chevron-down"></i>
                        </button>
                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-44 bg-white border border-border-color shadow rounded-md mt-2 z-10" role="menu">
                            <div class="p-2 space-y-1">
                                <a class="block px-2 py-1.5 text-sm rounded hover:bg-light text-gray-900" href="#">Due Date: Earliest First</a>
                                <a class="block px-2 py-1.5 text-sm rounded hover:bg-light text-gray-900" href="#">Balance: Highest First</a>
                                <a class="block px-2 py-1.5 text-sm rounded hover:bg-light text-gray-900" href="#">Vendor: A to Z</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payables Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-light/60 border-y border-border-color text-default font-medium uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-2.5 px-2">Vendor Name</th>
                            <th class="py-2.5 px-2">Bill / Invoice #</th>
                            <th class="py-2.5 px-2">PO Reference</th>
                            <th class="py-2.5 px-2">Due Date</th>
                            <th class="py-2.5 px-2">Total Amount</th>
                            <th class="py-2.5 px-2">Balance Due</th>
                            <th class="py-2.5 px-2">Aging Bucket</th>
                            <th class="py-2.5 px-2">3-Way Match</th>
                            <th class="py-2.5 px-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-7 rounded bg-primary-transparent text-primary flex items-center justify-center font-bold text-xs">AM</div>
                                    <div>
                                        <span class="font-semibold text-title block">Apex Microtech Ltd</span>
                                        <span class="text-[11px] text-default">Silicon & PCB Components</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 font-mono text-primary font-medium">#VBL-2026-0182</td>
                            <td class="py-2.5 px-2 font-mono text-default">#PO-2026-0041</td>
                            <td class="py-2.5 px-2 text-default">28 Jan 2026</td>
                            <td class="py-2.5 px-2 text-default">$48,200.00</td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$48,200.00</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Current (Due in 8d)</span></td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-semibold inline-flex items-center gap-1"><i class="ph ph-check"></i> 3-Way Matched</span></td>
                            <td class="py-2.5 px-2 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="btn-sm bg-dark text-white border border-dark hover:bg-primary-hover hover:border-primary-hover cursor-pointer" data-hs-overlay="#record-payment-modal">Pay</button>
                                    <button type="button" class="size-7 rounded-md border border-border-color hover:bg-light cursor-pointer flex items-center justify-center text-default" data-hs-overlay="#approve-bill-modal"><i class="ph ph-eye"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-7 rounded bg-info-transparent text-info flex items-center justify-center font-bold text-xs">SZ</div>
                                    <div>
                                        <span class="font-semibold text-title block">Shenzhen FastPCB Co.</span>
                                        <span class="text-[11px] text-default">Sub-Assembly Modules</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 font-mono text-primary font-medium">#VBL-2026-0189</td>
                            <td class="py-2.5 px-2 font-mono text-default">#PO-2026-0045</td>
                            <td class="py-2.5 px-2 text-default">05 Feb 2026</td>
                            <td class="py-2.5 px-2 text-default">$32,500.00</td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$32,500.00</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Current (Due in 16d)</span></td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-semibold inline-flex items-center gap-1"><i class="ph ph-check"></i> 3-Way Matched</span></td>
                            <td class="py-2.5 px-2 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="btn-sm bg-dark text-white border border-dark hover:bg-primary-hover hover:border-primary-hover cursor-pointer" data-hs-overlay="#record-payment-modal">Pay</button>
                                    <button type="button" class="size-7 rounded-md border border-border-color hover:bg-light cursor-pointer flex items-center justify-center text-default" data-hs-overlay="#approve-bill-modal"><i class="ph ph-eye"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-7 rounded bg-warning-transparent text-warning flex items-center justify-center font-bold text-xs">GM</div>
                                    <div>
                                        <span class="font-semibold text-title block">Global Metals & Alloys</span>
                                        <span class="text-[11px] text-default">Aluminum Extrusions</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 font-mono text-primary font-medium">#VBL-2025-0912</td>
                            <td class="py-2.5 px-2 font-mono text-default">#PO-2025-0810</td>
                            <td class="py-2.5 px-2 text-default">12 Dec 2025</td>
                            <td class="py-2.5 px-2 text-default">$28,400.00</td>
                            <td class="py-2.5 px-2 font-bold text-warning">$18,400.00</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-medium">31–60 Days Overdue</span></td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-info-transparent text-info px-2 py-0.5 rounded font-medium inline-flex items-center gap-1"><i class="ph ph-info"></i> Partial Payment</span></td>
                            <td class="py-2.5 px-2 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="btn-sm bg-dark text-white border border-dark hover:bg-primary-hover hover:border-primary-hover cursor-pointer" data-hs-overlay="#record-payment-modal">Pay</button>
                                    <button type="button" class="size-7 rounded-md border border-border-color hover:bg-light cursor-pointer flex items-center justify-center text-default" data-hs-overlay="#approve-bill-modal"><i class="ph ph-eye"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-7 rounded bg-danger-transparent text-danger flex items-center justify-center font-bold text-xs">TC</div>
                                    <div>
                                        <span class="font-semibold text-title block">Toray Composite Plastics</span>
                                        <span class="text-[11px] text-default">Injection Molded Casings</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 font-mono text-primary font-medium">#VBL-2025-0844</td>
                            <td class="py-2.5 px-2 font-mono text-default">#PO-2025-0720</td>
                            <td class="py-2.5 px-2 text-default">15 Nov 2025</td>
                            <td class="py-2.5 px-2 text-default">$15,200.00</td>
                            <td class="py-2.5 px-2 font-bold text-danger">$15,200.00</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-danger-transparent text-danger px-2 py-0.5 rounded font-bold">Over 90 Days Hold</span></td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-danger-transparent text-danger px-2 py-0.5 rounded font-semibold inline-flex items-center gap-1"><i class="ph ph-warning-octagon"></i> Quality Hold (NCR)</span></td>
                            <td class="py-2.5 px-2 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 hover:bg-light cursor-pointer" data-hs-overlay="#approve-bill-modal">Inspect</button>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-7 rounded bg-purple/10 text-purple flex items-center justify-center font-bold text-xs">EX</div>
                                    <div>
                                        <span class="font-semibold text-title block">Express Logistics Freight</span>
                                        <span class="text-[11px] text-default">Inbound Freight & Customs</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 font-mono text-primary font-medium">#VBL-2026-0201</td>
                            <td class="py-2.5 px-2 font-mono text-default">#PO-2026-0052</td>
                            <td class="py-2.5 px-2 text-default">30 Jan 2026</td>
                            <td class="py-2.5 px-2 text-default">$14,000.00</td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$14,000.00</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Current (Due in 10d)</span></td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-semibold inline-flex items-center gap-1"><i class="ph ph-check"></i> 3-Way Matched</span></td>
                            <td class="py-2.5 px-2 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="btn-sm bg-dark text-white border border-dark hover:bg-primary-hover hover:border-primary-hover cursor-pointer" data-hs-overlay="#record-payment-modal">Pay</button>
                                    <button type="button" class="size-7 rounded-md border border-border-color hover:bg-light cursor-pointer flex items-center justify-center text-default" data-hs-overlay="#approve-bill-modal"><i class="ph ph-eye"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="flex items-center justify-between py-3 border-t border-border-color flex-wrap gap-2 text-xs text-default mt-2">
                <span>Showing 1 to 5 of 28 vendor bills</span>
                <div class="flex items-center gap-1">
                    <button type="button" class="size-7 rounded-md text-sm border border-border-color flex items-center justify-center cursor-pointer text-default hover:bg-light" aria-label="Previous"><i class="ph ph-caret-left text-sm"></i></button>
                    <button type="button" class="size-7 rounded-md text-xs bg-dark text-white flex items-center justify-center cursor-pointer">1</button>
                    <button type="button" class="size-7 rounded-md text-xs border border-border-color flex items-center justify-center cursor-pointer text-default hover:bg-light">2</button>
                    <button type="button" class="size-7 rounded-md text-xs border border-border-color flex items-center justify-center cursor-pointer text-default hover:bg-light">3</button>
                    <button type="button" class="size-7 rounded-md text-sm border border-border-color flex items-center justify-center cursor-pointer text-default hover:bg-light" aria-label="Next"><i class="ph ph-caret-right text-sm"></i></button>
                </div>
            </div>

        </div>

    </div>
    <!-- End Content -->

@endsection
