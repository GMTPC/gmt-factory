@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">

        <!-- Page Header -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 lg:mb-6">
            <div>
                <h1 class="text-gray-900 text-xl font-bold mb-1">Bill of Materials (BOM)</h1>
                <p class="text-xs text-default mb-0">Define production recipes, multi-level component formulas, scrap factors, and assembly costing</p>
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
                <button type="button" class="btn-sm bg-dark text-white border border-dark inline-flex items-center gap-2 hover:bg-primary-hover hover:border-primary-hover cursor-pointer" data-hs-overlay="#add-bom-modal">
                    <i class="ph ph-plus"></i> Create New BOM
                </button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Active BOM Recipes</p>
                        <h2 class="text-xl font-bold text-gray-900 mb-0">64</h2>
                    </div>
                    <div class="size-9 rounded-md bg-primary-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-tree-structure text-primary text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0 flex items-center gap-1">
                    <span class="text-success inline-flex items-center"><i class="ph ph-check"></i> Standardized</span> production formulas
                </p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Under Engineering Revision</p>
                        <h2 class="text-xl font-bold text-warning mb-0">8</h2>
                    </div>
                    <div class="size-9 rounded-md bg-warning-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-git-branch text-warning text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-warning mb-0 font-medium">Pending ECO engineering sign-off</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Avg Assembly Cost</p>
                        <h2 class="text-xl font-bold text-gray-900 mb-0">$142.50</h2>
                    </div>
                    <div class="size-9 rounded-md bg-info-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-calculator text-info text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0 flex items-center gap-1">
                    <span class="text-success inline-flex items-center"><i class="ph ph-trend-down"></i> -3.2%</span> efficiency gains
                </p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Tracked Component SKUs</p>
                        <h2 class="text-xl font-bold text-gray-900 mb-0">480</h2>
                    </div>
                    <div class="size-9 rounded-md bg-purple-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-cube text-purple text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0">Raw materials & sub-assemblies</p>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="bg-white border border-border-color rounded-md p-4 pb-0">

            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="relative w-64 search-input">
                        <i class="ph ph-magnifying-glass absolute right-2.5 top-1/2 -translate-y-1/2 text-default text-sm"></i>
                        <input type="text" class="w-full px-3 pe-8 py-2 h-7 text-[12px]! border border-border-color rounded-md bg-white focus:outline-none focus:ring-0" placeholder="Search product or BOM code...">
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
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Active</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Under Revision</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0"> Archived</label>
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
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Cost: High to Low</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Cost: Low to High</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">Components: Most to Least</a>
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
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">BOM ID</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Finished Product</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Revision</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Components</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Material Cost</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Labor & Overhead</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Total Unit Cost</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Status</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#BOM-1001</td>
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-8 rounded-md bg-light flex items-center justify-center shrink-0">
                                        <i class="ph-duotone ph-desktop-tower text-primary text-base"></i>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-title block">Apex Pro Desktop Tower</span>
                                        <span class="text-[11px] text-default">SKU: PROD-APX-01</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2"><span class="text-xs bg-light px-2 py-0.5 rounded border border-border-color font-mono">v2.1</span></td>
                            <td class="py-2.5 px-2 text-default font-medium">14 Parts</td>
                            <td class="py-2.5 px-2 text-default">$320.00</td>
                            <td class="py-2.5 px-2 text-default">$45.00</td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$365.00</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Active</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#view-recipe-modal"><i class="icon-eye"></i> View Recipe Tree</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="{{url('work-orders')}}"><i class="ph ph-wrench"></i> Create Work Order</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-danger hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#delete-modal"><i class="icon-trash-2"></i> Archive Recipe</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#BOM-1002</td>
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-8 rounded-md bg-light flex items-center justify-center shrink-0">
                                        <i class="ph-duotone ph-headphones text-info text-base"></i>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-title block">Wireless Studio Headset</span>
                                        <span class="text-[11px] text-default">SKU: PROD-WSH-09</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2"><span class="text-xs bg-light px-2 py-0.5 rounded border border-border-color font-mono">v1.4</span></td>
                            <td class="py-2.5 px-2 text-default font-medium">9 Parts</td>
                            <td class="py-2.5 px-2 text-default">$48.50</td>
                            <td class="py-2.5 px-2 text-default">$14.00</td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$62.50</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Active</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#view-recipe-modal"><i class="icon-eye"></i> View Recipe Tree</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="{{url('work-orders')}}"><i class="ph ph-wrench"></i> Create Work Order</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#BOM-1003</td>
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-8 rounded-md bg-light flex items-center justify-center shrink-0">
                                        <i class="ph-duotone ph-device-mobile text-warning text-base"></i>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-title block">Industrial Scanner Terminal</span>
                                        <span class="text-[11px] text-default">SKU: PROD-IST-44</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2"><span class="text-xs bg-light px-2 py-0.5 rounded border border-border-color font-mono">v3.0-rc</span></td>
                            <td class="py-2.5 px-2 text-default font-medium">21 Parts</td>
                            <td class="py-2.5 px-2 text-default">$210.00</td>
                            <td class="py-2.5 px-2 text-default">$60.00</td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$270.00</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-medium">Under Revision</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#view-recipe-modal"><i class="icon-eye"></i> View Recipe Tree</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-success hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#"><i class="ph ph-check"></i> Approve Revision</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#BOM-1004</td>
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-8 rounded-md bg-light flex items-center justify-center shrink-0">
                                        <i class="ph-duotone ph-printer text-success text-base"></i>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-title block">POS Thermal Receipt Printer</span>
                                        <span class="text-[11px] text-default">SKU: PROD-PRN-88</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2"><span class="text-xs bg-light px-2 py-0.5 rounded border border-border-color font-mono">v1.1</span></td>
                            <td class="py-2.5 px-2 text-default font-medium">12 Parts</td>
                            <td class="py-2.5 px-2 text-default">$74.00</td>
                            <td class="py-2.5 px-2 text-default">$18.50</td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$92.50</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Active</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#view-recipe-modal"><i class="icon-eye"></i> View Recipe Tree</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="{{url('work-orders')}}"><i class="ph ph-wrench"></i> Create Work Order</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#BOM-1005</td>
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-8 rounded-md bg-light flex items-center justify-center shrink-0">
                                        <i class="ph-duotone ph-broadcast text-purple text-base"></i>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-title block">Enterprise Mesh WiFi Gateway</span>
                                        <span class="text-[11px] text-default">SKU: PROD-GWY-55</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2"><span class="text-xs bg-light px-2 py-0.5 rounded border border-border-color font-mono">v1.0</span></td>
                            <td class="py-2.5 px-2 text-default font-medium">16 Parts</td>
                            <td class="py-2.5 px-2 text-default">$115.00</td>
                            <td class="py-2.5 px-2 text-default">$28.00</td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$143.00</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Active</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#view-recipe-modal"><i class="icon-eye"></i> View Recipe Tree</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="{{url('work-orders')}}"><i class="ph ph-wrench"></i> Create Work Order</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-primary">#BOM-1006</td>
                            <td class="py-2.5 px-2">
                                <div class="flex items-center gap-2">
                                    <div class="size-8 rounded-md bg-light flex items-center justify-center shrink-0">
                                        <i class="ph-duotone ph-armchair text-orange text-base"></i>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-title block">Ergonomic Executive Office Chair</span>
                                        <span class="text-[11px] text-default">SKU: PROD-CHR-02</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-2"><span class="text-xs bg-light px-2 py-0.5 rounded border border-border-color font-mono">v2.0</span></td>
                            <td class="py-2.5 px-2 text-default font-medium">8 Parts</td>
                            <td class="py-2.5 px-2 text-default">$95.00</td>
                            <td class="py-2.5 px-2 text-default">$22.00</td>
                            <td class="py-2.5 px-2 font-bold text-gray-900">$117.00</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Active</span></td>
                            <td class="py-2.5 px-2">
                                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm size-7 bg-white border border-border-color text-gray-600 inline-flex items-center justify-center hover:bg-light hover:border-light hover:text-gray-900 focus:bg-light focus:border-light focus:text-gray-900 focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown"><i class="icon-ellipsis-vertical font-normal"></i></button>
                                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <div class="p-2 space-y-1">
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#" data-hs-overlay="#view-recipe-modal"><i class="icon-eye"></i> View Recipe Tree</a>
                                            <a class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="{{url('work-orders')}}"><i class="ph ph-wrench"></i> Create Work Order</a>
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
