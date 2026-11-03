@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">

        <!-- Profile & Live Attendance Banner -->
        <div class="bg-white border border-border-color rounded-md p-4 mb-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="size-16 rounded-full bg-primary-transparent text-primary flex items-center justify-center font-bold text-xl ring-2 ring-primary shrink-0">
                        MV
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-gray-900 text-xl font-bold mb-0">Marcus Vance</h1>
                            <span class="text-[11px] bg-primary-transparent text-primary px-2 py-0.5 rounded font-medium">EMP-1042</span>
                        </div>
                        <p class="text-xs text-default mb-1">Lead Software Architect &bull; Engineering & Technology Department</p>
                        <p class="text-[11px] text-default mb-0 flex items-center gap-2">
                            <span><i class="ph ph-envelope"></i> marcus.vance@dreamserp.com</span>
                            <span>&bull;</span>
                            <span><i class="ph ph-map-pin"></i> HQ - San Francisco, CA</span>
                        </p>
                    </div>
                </div>

                <!-- Punch In/Out Card -->
                <div class="bg-light border border-border-color rounded-md p-3 flex items-center gap-4 flex-wrap sm:flex-nowrap">
                    <div>
                        <p class="text-[11px] text-default mb-0 uppercase tracking-wider font-semibold">Today's Shift: 09:00 AM - 06:00 PM</p>
                        <div class="flex items-center gap-2 my-1">
                            <span class="size-2.5 rounded-full bg-success animate-pulse"></span>
                            <span class="text-base font-bold text-gray-900">04 hrs 28 mins</span>
                            <span class="text-xs text-default">(Logged In)</span>
                        </div>
                        <p class="text-[11px] text-default mb-0">Clocked in today at 08:58 AM</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn-sm bg-danger text-white border border-danger hover:bg-danger-hover cursor-pointer inline-flex items-center gap-1.5">
                            <i class="ph ph-sign-out"></i> Punch Out
                        </button>
                        <button type="button" class="btn-sm bg-dark text-white border border-dark hover:bg-primary-hover hover:border-primary-hover cursor-pointer inline-flex items-center gap-1.5" data-hs-overlay="#apply-leave-modal">
                            <i class="ph ph-calendar-plus"></i> Apply Leave
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leave Balances & KPI Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Annual Leave Balance</p>
                        <h2 class="text-xl font-bold text-primary mb-0">14 Days</h2>
                    </div>
                    <div class="size-9 rounded-md bg-primary-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-airplane-tilt text-primary text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0">Total allocation: 20 days/yr</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Sick & Casual Leaves</p>
                        <h2 class="text-xl font-bold text-success mb-0">6 Days</h2>
                    </div>
                    <div class="size-9 rounded-md bg-success-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-first-aid text-success text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-default mb-0">No documentation needed for &lt;2d</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Monthly Attendance</p>
                        <h2 class="text-xl font-bold text-info mb-0">98.4%</h2>
                    </div>
                    <div class="size-9 rounded-md bg-info-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-calendar-check text-info text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-success mb-0 font-medium">Perfect on-time record</p>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-xs text-default mb-1">Pending Requests</p>
                        <h2 class="text-xl font-bold text-warning mb-0">2 Items</h2>
                    </div>
                    <div class="size-9 rounded-md bg-warning-transparent flex items-center justify-center shrink-0">
                        <i class="ph-duotone ph-hourglass-medium text-warning text-lg"></i>
                    </div>
                </div>
                <p class="text-[11px] text-warning mb-0">1 Expense claim, 1 Timesheet</p>
            </div>
        </div>

        <!-- Tabs Section -->
        <div class="bg-white border border-border-color rounded-md p-4">

            <!-- Nav Tabs -->
            <div class="flex items-center gap-2 border-b border-border-color pb-3 mb-4 overflow-x-auto">
                <button type="button" class="btn-sm bg-dark text-white border border-dark inline-flex items-center gap-1.5 cursor-pointer">
                    <i class="ph ph-clock"></i> Recent Attendance Log
                </button>
                <button type="button" class="btn-sm bg-white text-gray-900 border border-border-color hover:bg-light inline-flex items-center gap-1.5 cursor-pointer" data-hs-overlay="#apply-leave-modal">
                    <i class="ph ph-calendar-blank"></i> My Leave Applications
                </button>
                <button type="button" class="btn-sm bg-white text-gray-900 border border-border-color hover:bg-light inline-flex items-center gap-1.5 cursor-pointer" data-hs-overlay="#payslip-modal">
                    <i class="ph ph-receipt"></i> Payslips & Tax Documents
                </button>
                <button type="button" class="btn-sm bg-white text-gray-900 border border-border-color hover:bg-light inline-flex items-center gap-1.5 cursor-pointer" data-hs-overlay="#expense-claim-modal">
                    <i class="ph ph-currency-dollar"></i> Submit Expense Claim
                </button>
            </div>

            <!-- Section: Recent Attendance Log -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-sm text-default border-b border-border-color">
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Date</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Shift</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Clock In</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Clock Out</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Working Hours</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Overtime</th>
                            <th class="text-left py-2 px-2 font-semibold text-gray-900">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-title">Today, 20 Jan 2026</td>
                            <td class="py-2.5 px-2 text-default">Regular Day Shift (09:00 - 18:00)</td>
                            <td class="py-2.5 px-2 font-medium text-success">08:58 AM</td>
                            <td class="py-2.5 px-2 text-default font-mono">--:--</td>
                            <td class="py-2.5 px-2 font-medium text-gray-900">04 hrs 28 mins</td>
                            <td class="py-2.5 px-2 text-default">0.0 hrs</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">On Duty</span></td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-title">Yesterday, 19 Jan 2026</td>
                            <td class="py-2.5 px-2 text-default">Regular Day Shift (09:00 - 18:00)</td>
                            <td class="py-2.5 px-2 font-medium text-success">08:55 AM</td>
                            <td class="py-2.5 px-2 font-medium text-gray-900">06:15 PM</td>
                            <td class="py-2.5 px-2 font-medium text-gray-900">08 hrs 20 mins</td>
                            <td class="py-2.5 px-2 text-success font-medium">+15 mins</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Present</span></td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-title">Friday, 16 Jan 2026</td>
                            <td class="py-2.5 px-2 text-default">Regular Day Shift (09:00 - 18:00)</td>
                            <td class="py-2.5 px-2 font-medium text-success">09:00 AM</td>
                            <td class="py-2.5 px-2 font-medium text-gray-900">06:05 PM</td>
                            <td class="py-2.5 px-2 font-medium text-gray-900">08 hrs 05 mins</td>
                            <td class="py-2.5 px-2 text-default">0.0 hrs</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Present</span></td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-title">Thursday, 15 Jan 2026</td>
                            <td class="py-2.5 px-2 text-default">Regular Day Shift (09:00 - 18:00)</td>
                            <td class="py-2.5 px-2 font-medium text-success">08:52 AM</td>
                            <td class="py-2.5 px-2 font-medium text-gray-900">07:00 PM</td>
                            <td class="py-2.5 px-2 font-medium text-gray-900">09 hrs 08 mins</td>
                            <td class="py-2.5 px-2 text-success font-medium">+1 hr 08 mins</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Present</span></td>
                        </tr>
                        <tr class="border-b border-border-color hover:bg-light/40">
                            <td class="py-2.5 px-2 font-semibold text-title">Wednesday, 14 Jan 2026</td>
                            <td class="py-2.5 px-2 text-default">Regular Day Shift (09:00 - 18:00)</td>
                            <td class="py-2.5 px-2 font-medium text-success">08:56 AM</td>
                            <td class="py-2.5 px-2 font-medium text-gray-900">06:02 PM</td>
                            <td class="py-2.5 px-2 font-medium text-gray-900">08 hrs 06 mins</td>
                            <td class="py-2.5 px-2 text-default">0.0 hrs</td>
                            <td class="py-2.5 px-2"><span class="text-[11px] bg-success-transparent text-success px-2 py-0.5 rounded font-medium">Present</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Company Notices Banner inside Portal -->
            <div class="mt-4 p-3 bg-light border border-border-color rounded-md flex items-center justify-between flex-wrap gap-2 text-xs">
                <div class="flex items-center gap-2">
                    <i class="ph-duotone ph-megaphone text-primary text-lg"></i>
                    <span class="text-default"><strong class="text-gray-900">Company Townhall Announcement:</strong> Q1 2026 Strategic Roadmap presentation scheduled for Friday at 03:00 PM.</span>
                </div>
                <span class="text-default font-medium">Published by HR Operations</span>
            </div>

        </div>

    </div>
    <!-- End Content -->

@endsection
