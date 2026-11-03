@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">

        <!-- Page Header -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 lg:mb-6">
            <div>
                <h1 class="text-gray-900 text-xl font-bold mb-1">Request for Quotation (RFQ)</h1>
                <p class="text-xs text-default mb-0">Solicit supplier tenders, compare vendor bids side-by-side, and award purchase orders</p>
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
                <button type="button" class="btn-sm bg-dark text-white border border-dark inline-flex items-center gap-2 hover:bg-primary-hover hover:border-primary-hover cursor-pointer" data-hs-overlay="#add-rfq-modal">
                    <i class="ph ph-plus"></i> Create RFQ
                </button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Active RFQs</p>
                        <h2 class="text-xl font-bold text-gray-900 mb-0">19 Bids Open</h2>
                    </div>
                    <div class="size-9 rounded-md bg-primary-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-file-magnifying-glass text-primary text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0 flex items-center gap-1">
                    <span class="text-success inline-flex items-center"><i class="ph ph-trend-up"></i> +5</span> launched this week
                </p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Quotes Received</p>
                        <h2 class="text-xl font-bold text-info mb-0">34 Proposals</h2>
                    </div>
                    <div class="size-9 rounded-md bg-info-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-users-three text-info text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0">Avg 2.8 quotes per tender</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Avg Bidding Savings</p>
                        <h2 class="text-xl font-bold text-success mb-0">14.8%</h2>
                    </div>
                    <div class="size-9 rounded-md bg-success-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-percent text-success text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-success mb-0 font-medium">Below baseline budget</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Sourcing Pipeline Value</p>
                        <h2 class="text-xl font-bold text-gray-900 mb-0">$680,000</h2>
                    </div>
                    <div class="size-9 rounded-md bg-warning-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-handshake text-warning text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0">Pending procurement decisions</p>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="bg-white border border-border-color rounded-md p-4 pb-0">

            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="relative w-64 search-input">
                        <i class="ph ph-magnifying-glass absolute right-2.5 top-1/2 -translate-y-1/2 text-default text-sm"></i>
                        <input type="text" class="w-full px-3 pe-8 py-2 h-7 text-[12px]! border border-border-color rounded-md bg-white focus:outline-none focus:ring-0" placeholder="Search RFQ # or item...">
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
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Open / Bidding</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Under Review</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0"> Awarded</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0"> Closed</label>
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
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Closing Date: Earliest</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Budget: High to Low</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Quotes: Most Received</a>
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
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">RFQ Code</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Procurement Title</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Target Qty</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Budget Est.</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Invited Vendors</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Quotes In</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Lowest Bid</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Deadline</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Status</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#RFQ-2026-041</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">Semiconductor Motherboard Chipsets</span>
                                <span class="text-[11px] text-default">Category: Electronic Components</span>
                            </td>
                            <td class="py-2.5 px-2 text-default">5,000 Units</td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">$185,000.00</td>
                            <td class="py-2.5 px-2 text-default">4 Vendors</td>
                            <td class="py-2.5 px-2 font-semibold text-info">3 Received</td>
                            <td class="py-2.5 px-2 text-success font-semibold">$162,500.00</td>
                            <td class="py-2.5 px-2 text-default">25 Jan 2026</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-medium">Under Review</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#compare-quotes-modal"><i class="ph ph-scales"></i> Compare Quotes</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-primary hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="{{url('add-purchase-order')}}"><i class="ph ph-shopping-cart"></i> Award & Create PO</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#RFQ-2026-040</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">Anodized Aluminum Computer Casings</span>
                                <span class="text-[11px] text-default">Category: Raw Metal & Enclosures</span>
                            </td>
                            <td class="py-2.5 px-2 text-default">2,000 Units</td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">$75,000.00</td>
                            <td class="py-2.5 px-2 text-default">5 Vendors</td>
                            <td class="py-2.5 px-2 font-semibold text-info">2 Received</td>
                            <td class="py-2.5 px-2 text-success font-semibold">$68,200.00</td>
                            <td class="py-2.5 px-2 text-default">28 Jan 2026</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-primary-transparent text-primary px-2 py-0.5 rounded font-medium">Open Bidding</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#compare-quotes-modal"><i class="ph ph-scales"></i> Compare Quotes</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-paper-plane-tilt"></i> Send Vendor Reminder</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#RFQ-2026-039</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">Industrial Packaging & Bubble Cushions</span>
                                <span class="text-[11px] text-default">Category: Logistics & Packaging</span>
                            </td>
                            <td class="py-2.5 px-2 text-default">10,000 Sets</td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">$22,000.00</td>
                            <td class="py-2.5 px-2 text-default">3 Vendors</td>
                            <td class="py-2.5 px-2 font-semibold text-success">3 Received</td>
                            <td class="py-2.5 px-2 text-success font-semibold">$19,400.00</td>
                            <td class="py-2.5 px-2 text-default">16 Jan 2026</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Awarded</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="{{url('purchase-orders')}}"><i class="icon-eye"></i> View Purchase Order</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#RFQ-2026-038</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">High-Torque Stepper Motors NEMA 23</span>
                                <span class="text-[11px] text-default">Category: Robotics & Motors</span>
                            </td>
                            <td class="py-2.5 px-2 text-default">800 Units</td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">$48,000.00</td>
                            <td class="py-2.5 px-2 text-default">4 Vendors</td>
                            <td class="py-2.5 px-2 font-semibold text-info">1 Received</td>
                            <td class="py-2.5 px-2 text-default">$47,500.00</td>
                            <td class="py-2.5 px-2 text-default">30 Jan 2026</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-primary-transparent text-primary px-2 py-0.5 rounded font-medium">Open Bidding</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#compare-quotes-modal"><i class="ph ph-scales"></i> Compare Quotes</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#RFQ-2026-037</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">Li-Ion Battery Packs 12V 5000mAh</span>
                                <span class="text-[11px] text-default">Category: Power & Cells</span>
                            </td>
                            <td class="py-2.5 px-2 text-default">3,500 Units</td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">$98,000.00</td>
                            <td class="py-2.5 px-2 text-default">3 Vendors</td>
                            <td class="py-2.5 px-2 font-semibold text-success">3 Received</td>
                            <td class="py-2.5 px-2 text-success font-semibold">$89,600.00</td>
                            <td class="py-2.5 px-2 text-default">14 Jan 2026</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Awarded</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="{{url('purchase-orders')}}"><i class="icon-eye"></i> View Purchase Order</a>
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
