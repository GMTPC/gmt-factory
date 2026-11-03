@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">

        <!-- Page Header -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 lg:mb-6">
            <div>
                <h1 class="text-gray-900 text-xl font-bold mb-1">Quality Assurance &amp; Inspections</h1>
                <p class="text-xs text-default mb-0">Track lot-level quality checks, incoming &amp; outgoing tolerances, defect rates, and Non-Conformance Reports (NCR)</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-light cursor-pointer">
                    <i class="ph-duotone ph-printer"></i> Print QA Log
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
                <button type="button" class="btn-sm bg-white border border-danger text-danger inline-flex items-center gap-2 hover:bg-danger-transparent cursor-pointer" data-hs-overlay="#ncr-modal">
                    <i class="ph ph-warning-octagon"></i> Raise NCR
                </button>
                <button type="button" class="btn-sm bg-dark text-white border border-dark inline-flex items-center gap-2 hover:bg-primary-hover hover:border-primary-hover cursor-pointer" data-hs-overlay="#add-inspection-modal">
                    <i class="ph ph-plus"></i> New Inspection Log
                </button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">First-Pass Yield</p>
                        <h2 class="text-xl font-bold text-success mb-0">98.42%</h2>
                    </div>
                    <div class="size-9 rounded-md bg-success-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-check-circle text-success text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0 flex items-center gap-1">
                    <span class="text-success font-medium">+0.65%</span> vs previous 30 days
                </p>
            </div>

            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Total Lots Inspected</p>
                        <h2 class="text-xl font-bold text-gray-900 mb-0">1,485 Lots</h2>
                    </div>
                    <div class="size-9 rounded-md bg-primary-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-clipboard-text text-primary text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0 flex items-center gap-1">
                    <span class="text-primary font-medium">18 Active Lines</span> (IQC, IPQC &amp; OQC)
                </p>
            </div>

            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Non-Conformance / Rejected</p>
                        <h2 class="text-xl font-bold text-danger mb-0">23 Lots</h2>
                    </div>
                    <div class="size-9 rounded-md bg-danger-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-shield-warning text-danger text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-danger mb-0 font-medium flex items-center gap-1">
                    <span>8 Open NCRs</span> pending MRB review
                </p>
            </div>

            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Defect Rate (PPM)</p>
                        <h2 class="text-xl font-bold text-info mb-0">142 PPM</h2>
                    </div>
                    <div class="size-9 rounded-md bg-info-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-gauge text-info text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0 flex items-center gap-1">
                    <span class="text-success font-medium">Below Target</span> (Target: &lt; 200 PPM)
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
                        <input type="text" class="w-full px-3 pe-8 py-2 h-7 text-[12px]! border border-border-color rounded-md bg-white focus:outline-none focus:ring-0" placeholder="Search lot, order, or part #...">
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
                            <i class="icon-funnel font-normal"></i> Stage <i class="icon-chevron-down"></i>
                        </button>
                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                            <div class="p-2 space-y-1">
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> IQC (Incoming)</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> IPQC (In-Process)</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> OQC (Outgoing)</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0"> FAI (First Article)</label>
                                <div class="flex items-center justify-between gap-2 mt-3">
                                    <button type="button" class="btn-sm bg-white w-full border border-border-color text-gray-900 flex items-center justify-center hover:bg-light cursor-pointer">Reset</button>
                                    <button type="button" class="btn-sm bg-dark w-full border border-dark text-white flex items-center justify-center hover:bg-primary hover:border-primary hover:text-white cursor-pointer">Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hs-dropdown [--placement:bottom-left] [--auto-close:inside] relative inline-flex">
                        <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-primary hover:border-primary hover:text-white focus:bg-primary focus:border-primary focus:text-white focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            <i class="icon-filter font-normal"></i> Verdict <i class="icon-chevron-down"></i>
                        </button>
                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                            <div class="p-2 space-y-1">
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Passed (Conforming)</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Conditional Acceptance</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Quarantine (MRB)</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Rejected (Failed)</label>
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
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Defects: High to Low</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Inspection ID: Ascending</a>
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
                            <th class="text-left py-2 px-2 font-semibold text-gray-900 w-10">
                                <input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0">
                            </th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Inspection ID</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Order / Lot #</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Component / Part</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Stage</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Sample (AQL)</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Measured Tolerance</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Verdict</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Inspector</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Date</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2">
                                <input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0">
                            </td>
                            <td class="py-2.5 px-2">
                                <a href="#" class="font-semibold text-primary hover:underline text-sm block">QC-2026-0891</a>
                                <span class="text-[11px] text-default">Run #1</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">WO-2026-0041</span>
                                <span class="text-[11px] text-default">LOT-99214-A</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">CNC Billet Casing 6061-T6</span>
                                <span class="text-[11px] text-default">SKU: CNC-CAS-004</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-primary-transparent text-primary px-2 py-0.5 rounded font-medium">IPQC (In-Line)</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">50 pcs</span>
                                <span class="text-[11px] text-success">0 defects (0.0%)</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-mono text-xs text-gray-900 block">Bore: 25.008mm</span>
                                <span class="text-[11px] text-default">Nom: 25.000 &plusmn; 0.02</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium inline-flex items-center gap-1">
                                    <i class="ph ph-check-circle"></i> Passed
                                </span>
                            </td>
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-7 rounded-full bg-primary-transparent text-primary flex items-center justify-center font-bold text-xs shrink-0">MB</div>
                                    <span class="text-sm text-gray-900 font-medium">Marcus Brody</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 text-default">26 Sep 2026</td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-eye"></i> View Checklist</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-certificate"></i> Print Certificate</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-danger hover:bg-light hover:text-danger focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#ncr-modal"><i class="ph ph-warning-octagon"></i> Raise NCR</a>
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
                                <a href="#" class="font-semibold text-primary hover:underline text-sm block">QC-2026-0890</a>
                                <span class="text-[11px] text-default">Run #1</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">PO-2026-0188</span>
                                <span class="text-[11px] text-default">SUPP-LOT-773</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">Microcontroller MCU-ARM4</span>
                                <span class="text-[11px] text-default">SKU: MCU-CORTEX-M4</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-info-transparent text-info px-2 py-0.5 rounded font-medium">IQC (Incoming)</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">125 pcs</span>
                                <span class="text-[11px] text-danger">4 solder voids</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-mono text-xs text-danger block">Lead Pitch: 0.44mm</span>
                                <span class="text-[11px] text-default">Nom: 0.500 &plusmn; 0.03</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-danger-transparent text-danger px-2 py-0.5 rounded font-medium inline-flex items-center gap-1">
                                    <i class="ph ph-x-circle"></i> Rejected
                                </span>
                            </td>
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-7 rounded-full bg-success-transparent text-success flex items-center justify-center font-bold text-xs shrink-0">SL</div>
                                    <span class="text-sm text-gray-900 font-medium">Sarah Lin</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 text-default">25 Sep 2026</td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-eye"></i> View Checklist</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-danger hover:bg-light hover:text-danger focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#ncr-modal"><i class="ph ph-warning-octagon"></i> View NCR-2026-012</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-arrow-u-up-left"></i> Return to Vendor</a>
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
                                <a href="#" class="font-semibold text-primary hover:underline text-sm block">QC-2026-0889</a>
                                <span class="text-[11px] text-default">Run #2</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">WO-2026-0039</span>
                                <span class="text-[11px] text-default">LOT-99182-B</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">Titanium Drive Shaft Axle</span>
                                <span class="text-[11px] text-default">SKU: AXLE-TI-09</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-medium">OQC (Final Out)</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">20 pcs</span>
                                <span class="text-[11px] text-warning">1 surface scratch</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-mono text-xs text-warning block">Roughness: Ra 0.85</span>
                                <span class="text-[11px] text-default">Nom: Ra &lt; 0.80 &mu;m</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-medium inline-flex items-center gap-1">
                                    <i class="ph ph-warning"></i> Quarantine
                                </span>
                            </td>
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">DH</div>
                                    <span class="text-sm text-gray-900 font-medium">David Harper</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 text-default">24 Sep 2026</td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-eye"></i> View Checklist</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-check-square"></i> Release Lot (MRB)</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-danger hover:bg-light hover:text-danger focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-trash"></i> Scrap Lot</a>
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
                                <a href="#" class="font-semibold text-primary hover:underline text-sm block">QC-2026-0888</a>
                                <span class="text-[11px] text-default">Run #1</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">WO-2026-0036</span>
                                <span class="text-[11px] text-default">LOT-99150-C</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">Lithium Cell 21700 Battery</span>
                                <span class="text-[11px] text-default">SKU: BAT-21700-12S</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-medium">OQC (Final Out)</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">80 pcs</span>
                                <span class="text-[11px] text-success">0 voltage leaks</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-mono text-xs text-gray-900 block">48.12 VDC Nominal</span>
                                <span class="text-[11px] text-default">Nom: 48.0 &plusmn; 0.5V</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium inline-flex items-center gap-1">
                                    <i class="ph ph-check-circle"></i> Passed
                                </span>
                            </td>
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-7 rounded-full bg-primary-transparent text-primary flex items-center justify-center font-bold text-xs shrink-0">MB</div>
                                    <span class="text-sm text-gray-900 font-medium">Marcus Brody</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 text-default">24 Sep 2026</td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-eye"></i> View Checklist</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-certificate"></i> Print Certificate</a>
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
                                <a href="#" class="font-semibold text-primary hover:underline text-sm block">QC-2026-0887</a>
                                <span class="text-[11px] text-default">Run #1</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">PO-2026-0182</span>
                                <span class="text-[11px] text-default">SUPP-LOT-441</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">Precision O-Ring Nitrile 70A</span>
                                <span class="text-[11px] text-default">SKU: SEAL-NBR-022</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-info-transparent text-info px-2 py-0.5 rounded font-medium">IQC (Incoming)</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">200 pcs</span>
                                <span class="text-[11px] text-info">2 minor flashings</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-mono text-xs text-gray-900 block">Hardness: 71 Shore A</span>
                                <span class="text-[11px] text-default">Nom: 70 &plusmn; 5 Shore</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-info-transparent text-info px-2 py-0.5 rounded font-medium inline-flex items-center gap-1">
                                    <i class="ph ph-info"></i> Conditional
                                </span>
                            </td>
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-7 rounded-full bg-success-transparent text-success flex items-center justify-center font-bold text-xs shrink-0">SL</div>
                                    <span class="text-sm text-gray-900 font-medium">Sarah Lin</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 text-default">23 Sep 2026</td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-eye"></i> View Checklist</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-pencil-simple"></i> Edit Concession</a>
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
                                <a href="#" class="font-semibold text-primary hover:underline text-sm block">QC-2026-0886</a>
                                <span class="text-[11px] text-default">Run #1</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">WO-2026-0031</span>
                                <span class="text-[11px] text-default">LOT-99099-F</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">Industrial Servo Actuator 750W</span>
                                <span class="text-[11px] text-default">SKU: MOT-SRV-750</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-primary-transparent text-primary px-2 py-0.5 rounded font-medium">IPQC (Sub-Assembly)</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-gray-900 text-sm block">30 pcs</span>
                                <span class="text-[11px] text-success">0 defects (0.0%)</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="font-mono text-xs text-gray-900 block">Torque: 2.41 Nm</span>
                                <span class="text-[11px] text-default">Nom: 2.40 &plusmn; 0.10</span>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium inline-flex items-center gap-1">
                                    <i class="ph ph-check-circle"></i> Passed
                                </span>
                            </td>
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">DH</div>
                                    <span class="text-sm text-gray-900 font-medium">David Harper</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-2 text-default">22 Sep 2026</td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-eye"></i> View Checklist</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-certificate"></i> Print Certificate</a>
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
