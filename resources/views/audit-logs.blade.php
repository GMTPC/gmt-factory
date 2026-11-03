@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">

        <!-- Page Header -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 lg:mb-6">
            <div>
                <h1 class="text-gray-900 text-xl font-bold mb-1">Audit Trail & Activity Logs</h1>
                <p class="text-xs text-default mb-0">Immutable compliance logs, administrative events, user modifications, and security access trails</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-light cursor-pointer">
                    <i class="ph-duotone ph-printer"></i> Print Log
                </button>
                <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                    <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-primary hover:border-primary hover:text-white focus:bg-primary focus:border-primary focus:text-white focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                        <i class="icon-download font-normal"></i> Export <i class="icon-chevron-down"></i>
                    </button>
                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                        <div class="p-2 space-y-1">
                            <a class="flex items-center px-2 py-1.5 rounded-md text-xs text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">
                                Export CSV (Compliance)
                            </a>
                            <a class="flex items-center px-2 py-1.5 rounded-md text-xs text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">
                                Export Signed PDF
                            </a>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-sm bg-dark text-white border border-dark inline-flex items-center gap-2 hover:bg-primary-hover hover:border-primary-hover cursor-pointer" data-hs-overlay="#retention-modal">
                    <i class="ph ph-shield-check"></i> Retention Policy
                </button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Total Security Events</p>
                        <h2 class="text-xl font-bold text-gray-900 mb-0">24,890</h2>
                    </div>
                    <div class="size-9 rounded-md bg-primary-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-list-dashes text-primary text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0 flex items-center gap-1">
                    <span class="text-success inline-flex items-center"><i class="ph ph-check"></i> Complete</span> audit retention active
                </p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Critical / Warning Flags</p>
                        <h2 class="text-xl font-bold text-warning mb-0">14 Alerts</h2>
                    </div>
                    <div class="size-9 rounded-md bg-warning-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-shield-warning text-warning text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-warning mb-0 font-medium">Failed logins & permission revokes</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Active Users Today</p>
                        <h2 class="text-xl font-bold text-info mb-0">128 Users</h2>
                    </div>
                    <div class="size-9 rounded-md bg-info-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-users text-info text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0">Across 6 global branch locations</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Compliance Status</p>
                        <h2 class="text-xl font-bold text-success mb-0">100% Passed</h2>
                    </div>
                    <div class="size-9 rounded-md bg-success-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-lock-key text-success text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-success mb-0 font-medium">SOC 2 & ISO 27001 Validated</p>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="bg-white border border-border-color rounded-md p-4 pb-0">

            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="relative w-64 search-input">
                        <i class="ph ph-magnifying-glass absolute right-2.5 top-1/2 -translate-y-1/2 text-default text-sm"></i>
                        <input type="text" class="w-full px-3 pe-8 py-2 h-7 text-[12px]! border border-border-color rounded-md bg-white focus:outline-none focus:ring-0" placeholder="Search user, IP, or entity...">
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
                            <i class="icon-funnel font-normal"></i> Severity <i class="icon-chevron-down"></i>
                        </button>
                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                            <div class="p-2 space-y-1">
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Information</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Warning</label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-md hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white"><input type="checkbox" class="size-4 rounded border-border-color text-primary focus:ring-0" checked> Critical</label>
                                <div class="flex items-center justify-between gap-2 mt-3">
                                    <button type="button" class="btn-sm bg-white w-full border border-border-color text-gray-900 flex items-center justify-center hover:bg-light cursor-pointer">Reset</button>
                                    <button type="button" class="btn-sm bg-dark w-full border border-dark text-white flex items-center justify-center hover:bg-primary hover:border-primary hover:text-white cursor-pointer">Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative inline-flex">
                        <button type="button" class="hs-dropdown-toggle cursor-pointer btn-sm bg-white border border-border-color text-gray-900 inline-flex items-center gap-2 hover:bg-primary hover:border-primary hover:text-white focus:bg-primary focus:border-primary focus:text-white focus:outline-hidden" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            <i class="icon-arrow-up-down font-normal"></i> Action Type <i class="icon-chevron-down"></i>
                        </button>
                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white border border-border-color shadow rounded-md mt-2 z-1" role="menu" aria-orientation="vertical" tabindex="-1">
                            <div class="p-2 space-y-1">
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">All Actions</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">UPDATE Operations</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">DELETE Operations</a>
                                <a class="flex items-center px-2 py-1.5 rounded-md text-sm text-gray-900 hover:bg-light hover:text-gray-900 focus:outline-hidden focus:bg-white" href="#">AUTH & Login Events</a>
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
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Timestamp</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">User / Actor</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Action</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Module / Entity</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Event Summary</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">IP Address & Agent</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Severity</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 text-default font-mono text-xs">2026-01-20 10:42:15</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">David Ross (CPA)</span>
                                <span class="text-[11px] text-default">Finance Director</span>
                            </td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-primary-transparent text-primary px-2 py-0.5 rounded font-mono font-medium">POST_JOURNAL</span></td>
                            <td class="py-2.5 px-2 text-default font-medium">Finance (#JV-2026-0091)</td>
                            <td class="py-2.5 px-2 text-default">Posted $18,500.00 depreciation voucher to General Ledger</td>
                            <td class="py-2.5 px-2 text-default font-mono text-xs">192.168.1.45 (San Jose)</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-info-transparent text-info px-2 py-0.5 rounded font-medium">Info</span></td>
                            <td class="py-2.5 px-2">
                                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 hover:bg-light cursor-pointer flex gap-2" data-hs-overlay="#view-diff-modal">
                                    <i class="icon-eye"></i> Details
                                </button>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 text-default font-mono text-xs">2026-01-20 09:30:02</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">Marcus Vance</span>
                                <span class="text-[11px] text-default">Lead Architect</span>
                            </td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-mono font-medium">LOGIN_SUCCESS</span></td>
                            <td class="py-2.5 px-2 text-default font-medium">Auth / Portal</td>
                            <td class="py-2.5 px-2 text-default">User logged in with 2FA TOTP verification token</td>
                            <td class="py-2.5 px-2 text-default font-mono text-xs">104.28.19.82 (SF, CA)</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-info-transparent text-info px-2 py-0.5 rounded font-medium">Info</span></td>
                            <td class="py-2.5 px-2">
                                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 hover:bg-light cursor-pointer flex gap-2" data-hs-overlay="#view-diff-modal">
                                    <i class="icon-eye"></i> Details
                                </button>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 text-default font-mono text-xs">2026-01-20 08:14:50</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">System Security Sentinel</span>
                                <span class="text-[11px] text-default">Automated Daemon</span>
                            </td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-danger-transparent text-danger px-2 py-0.5 rounded font-mono font-medium">AUTH_FAILED</span></td>
                            <td class="py-2.5 px-2 text-default font-medium">System / IAM</td>
                            <td class="py-2.5 px-2 text-default">3 consecutive invalid password attempts on user 'admin_backup'</td>
                            <td class="py-2.5 px-2 text-default font-mono text-xs">45.142.12.8 (Unknown VPN)</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-danger-transparent text-danger px-2 py-0.5 rounded font-bold">Critical</span></td>
                            <td class="py-2.5 px-2">
                                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 hover:bg-light cursor-pointer flex gap-2" data-hs-overlay="#view-diff-modal">
                                    <i class="icon-eye"></i> Details
                                </button>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 text-default font-mono text-xs">2026-01-19 16:55:12</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">Elena Rostova</span>
                                <span class="text-[11px] text-default">Security Admin</span>
                            </td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-mono font-medium">ROLE_UPDATE</span></td>
                            <td class="py-2.5 px-2 text-default font-medium">Roles & Permissions</td>
                            <td class="py-2.5 px-2 text-default">Elevated user 'j.smith' permission scope to 'Procurement Lead'</td>
                            <td class="py-2.5 px-2 text-default font-mono text-xs">192.168.1.10 (Austin)</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-medium">Warning</span></td>
                            <td class="py-2.5 px-2">
                                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 hover:bg-light cursor-pointer flex gap-2" data-hs-overlay="#view-diff-modal">
                                    <i class="icon-eye"></i> Details
                                </button>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 text-default font-mono text-xs">2026-01-19 14:20:00</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">Alex Rivera</span>
                                <span class="text-[11px] text-default">Inventory Controller</span>
                            </td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-purple-transparent text-purple px-2 py-0.5 rounded font-mono font-medium">STOCK_ADJUST</span></td>
                            <td class="py-2.5 px-2 text-default font-medium">Inventory (Warehouse A)</td>
                            <td class="py-2.5 px-2 text-default">Adjusted motherboard casing stock count: -15 units (physical count sync)</td>
                            <td class="py-2.5 px-2 text-default font-mono text-xs">192.168.2.14 (Chicago)</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-info-transparent text-info px-2 py-0.5 rounded font-medium">Info</span></td>
                            <td class="py-2.5 px-2">
                                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 hover:bg-light cursor-pointer flex gap-2" data-hs-overlay="#view-diff-modal">
                                    <i class="icon-eye"></i> Details
                                </button>
                            </td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 text-default font-mono text-xs">2026-01-19 11:05:42</td>
                            <td class="py-2.5 px-2">
                                <span class="font-semibold text-title block">Rachel Green</span>
                                <span class="text-[11px] text-default">Sales Executive</span>
                            </td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-primary-transparent text-primary px-2 py-0.5 rounded font-mono font-medium">DATA_EXPORT</span></td>
                            <td class="py-2.5 px-2 text-default font-medium">CRM Customers</td>
                            <td class="py-2.5 px-2 text-default">Exported full customer list to encrypted Excel file</td>
                            <td class="py-2.5 px-2 text-default font-mono text-xs">192.168.1.88 (New York)</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-warning-transparent text-warning px-2 py-0.5 rounded font-medium">Warning</span></td>
                            <td class="py-2.5 px-2">
                                <button type="button" class="btn-sm bg-white border border-border-color text-gray-900 hover:bg-light cursor-pointer flex gap-2" data-hs-overlay="#view-diff-modal">
                                    <i class="icon-eye"></i> Details
                                </button>
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
