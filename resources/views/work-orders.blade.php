@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">

        <!-- Page Header -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 lg:mb-6">
            <div>
                <h1 class="text-gray-900 text-xl font-bold mb-1">Work Orders (Manufacturing)</h1>
                <p class="text-xs text-default mb-0">Track factory floor production runs, assembly lines, unit outputs, and quality stages</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-light cursor-pointer">
                    <i class="ph-duotone ph-printer"></i> Print Job Cards
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
                <button type="button" class="btn-sm bg-dark text-white border border-dark inline-flex items-center gap-2 hover:bg-primary-hover hover:border-primary-hover cursor-pointer" data-hs-overlay="#add-workorder-modal">
                    <i class="ph ph-plus"></i> Create Work Order
                </button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Active Work Orders</p>
                        <h2 class="text-xl font-bold text-gray-900 mb-0">28 Orders</h2>
                    </div>
                    <div class="size-9 rounded-md bg-primary-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-gear-six text-primary text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0 flex items-center gap-1">
                    <span class="text-primary font-medium">1,840</span> finished units target
                </p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">In Production / Assembly</p>
                        <h2 class="text-xl font-bold text-info mb-0">14 Orders</h2>
                    </div>
                    <div class="size-9 rounded-md bg-info-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-wrench text-info text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0">Running on lines A1 to B3</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Quality Inspection Stage</p>
                        <h2 class="text-xl font-bold text-warning mb-0">6 Orders</h2>
                    </div>
                    <div class="size-9 rounded-md bg-warning-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-seal-check text-warning text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-warning mb-0 font-medium">Awaiting final QA certification</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">On-Time Completion</p>
                        <h2 class="text-xl font-bold text-success mb-0">94.2%</h2>
                    </div>
                    <div class="size-9 rounded-md bg-success-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-chart-line-up text-success text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0 flex items-center gap-1">
                    <span class="text-success font-medium">+1.8%</span> factory throughput
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
                        <input type="text" class="w-full px-3 pe-8 py-2 h-7 text-[12px]! border border-border-color rounded-md bg-white focus:outline-none focus:ring-0" placeholder="Search order # or line...">
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
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> In Progress</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Scheduled</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Quality Check</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0"> Completed</label>
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
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Due Date: Earliest</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Priority: High to Low</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Target Units: Largest</a>
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
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Work Order #</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Product & Recipe</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Target Qty</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Completed</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Progress</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Due Date</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Station / Line</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Priority</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Status</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#WO-2026-081</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">Apex Pro Desktop Tower</span>
                                <span class="text-[11px] text-default">BOM: #BOM-1001 (v2.1)</span>
                            </td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">200 Units</td>
                            <td class="py-2.5 px-2 text-default">160 Units</td>
                            <td class="py-2.5 px-2">
                                <div class="w-24">
                                    <div class="flex justify-between text-[11px] text-default mb-1">
                                        <span>80%</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-light rounded-full overflow-hidden">
                                        <div class="h-full bg-primary rounded-full w-[80%]"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 text-default">24 Jan 2026</td>
                            <td class="py-2.5 px-2 text-default font-medium">Assembly Line A1</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-danger-transparent text-danger px-2 py-0.5 rounded font-medium">High</span></td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-info-transparent text-info px-2 py-0.5 rounded font-medium">In Progress</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#progress-modal"><i class="ph ph-plus-circle"></i> Log Output Units</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#traveler-modal"><i class="icon-eye"></i> View Job Sheet</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#WO-2026-080</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">Wireless Studio Headset</span>
                                <span class="text-[11px] text-default">BOM: #BOM-1002 (v1.4)</span>
                            </td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">500 Units</td>
                            <td class="py-2.5 px-2 text-default">500 Units</td>
                            <td class="py-2.5 px-2">
                                <div class="w-24">
                                    <div class="flex justify-between text-[11px] text-default mb-1">
                                        <span>100%</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-light rounded-full overflow-hidden">
                                        <div class="h-full bg-warning rounded-full w-full"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 text-default">21 Jan 2026</td>
                            <td class="py-2.5 px-2 text-default font-medium">QA Bay 2</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-medium">Medium</span></td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-medium">Quality Check</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-success hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="{{url('manage-stock')}}"><i class="ph ph-check"></i> Certify QA & Send to Stock</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#traveler-modal"><i class="icon-eye"></i> View Job Sheet</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#WO-2026-079</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">POS Thermal Receipt Printer</span>
                                <span class="text-[11px] text-default">BOM: #BOM-1004 (v1.1)</span>
                            </td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">300 Units</td>
                            <td class="py-2.5 px-2 text-default">120 Units</td>
                            <td class="py-2.5 px-2">
                                <div class="w-24">
                                    <div class="flex justify-between text-[11px] text-default mb-1">
                                        <span>40%</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-light rounded-full overflow-hidden">
                                        <div class="h-full bg-primary rounded-full w-[40%]"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 text-default">27 Jan 2026</td>
                            <td class="py-2.5 px-2 text-default font-medium">Assembly Line B2</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-light text-default px-2 py-0.5 rounded font-medium border border-border-color">Normal</span></td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-info-transparent text-info px-2 py-0.5 rounded font-medium">In Progress</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#progress-modal"><i class="ph ph-plus-circle"></i> Log Output Units</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#traveler-modal"><i class="icon-eye"></i> View Job Sheet</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#WO-2026-078</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">Enterprise Mesh WiFi Gateway</span>
                                <span class="text-[11px] text-default">BOM: #BOM-1005 (v1.0)</span>
                            </td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">250 Units</td>
                            <td class="py-2.5 px-2 text-default">250 Units</td>
                            <td class="py-2.5 px-2">
                                <div class="w-24">
                                    <div class="flex justify-between text-[11px] text-default mb-1">
                                        <span>100%</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-light rounded-full overflow-hidden">
                                        <div class="h-full bg-success rounded-full w-full"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 text-default">18 Jan 2026</td>
                            <td class="py-2.5 px-2 text-default font-medium">Finished Warehouse</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-light text-default px-2 py-0.5 rounded font-medium border border-border-color">Normal</span></td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Completed</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#traveler-modal"><i class="icon-eye"></i> View Job Sheet</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="{{url('inventory')}}"><i class="ph ph-cube"></i> Check Inventory</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#WO-2026-077</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">Ergonomic Executive Office Chair</span>
                                <span class="text-[11px] text-default">BOM: #BOM-1006 (v2.0)</span>
                            </td>
                            <td class="py-2.5 px-2 font-semibold text-gray-900">150 Units</td>
                            <td class="py-2.5 px-2 text-default">0 Units</td>
                            <td class="py-2.5 px-2">
                                <div class="w-24">
                                    <div class="flex justify-between text-[11px] text-default mb-1">
                                        <span>0%</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-light rounded-full overflow-hidden">
                                        <div class="h-full bg-primary rounded-full w-0"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 text-default">30 Jan 2026</td>
                            <td class="py-2.5 px-2 text-default font-medium">Upholstery Line C1</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-medium">Medium</span></td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-purple-transparent text-purple px-2 py-0.5 rounded font-medium">Scheduled</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-primary hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-play"></i> Start Production</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#traveler-modal"><i class="icon-eye"></i> View Job Sheet</a>
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
