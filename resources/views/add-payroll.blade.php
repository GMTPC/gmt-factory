@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 lg:mb-6">
            <div>
                <div class="flex items-center gap-2 text-sm text-default mb-1">
                    <a href="{{url('index')}}" class="hover:text-primary">HRM</a>
                    <i class="ph ph-caret-right text-[10px]"></i>
                    <a href="{{url('payroll')}}" class="hover:text-primary">Payroll</a>
                    <i class="ph ph-caret-right text-[10px]"></i>
                    <span>Add Payroll</span>
                </div>
                <h1 class="text-gray-900 text-xl font-bold mb-0">Add Payroll</h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{url('payroll')}}" class="btn-sm bg-white border border-border-color text-gray-900 hover:bg-light cursor-pointer">Cancel</a>
                <button type="submit" class="btn-sm bg-dark text-white border border-dark hover:bg-primary-hover cursor-pointer">Save Payroll</button>
            </div>
        </div>
        <div class="grid grid-cols-12 gap-3">
            <div class="col-span-12 lg:col-span-8 space-y-3">
                <div class="bg-white border border-border-color rounded-md p-4">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Payroll Details</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-semibold text-gray-900 mb-1 block">Payroll ID</label>
                            <input type="text" class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-900 mb-1 block">Employee
                                <span class="text-danger">*</span>
                            </label>
                            <select class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
                                <option>Ethan Walker</option>
                                <option>Madison Clark</option>
                                <option>James Harris</option>
                                <option>Avery Thompson</option>
                                <option>Benjamin Wright</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-900 mb-1 block">Designation</label>
                            <select class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
                                <option>Manager</option>
                                <option>Developer</option>
                                <option>Designer</option>
                                <option>HR Manager</option>
                                <option>Accountant</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-900 mb-1 block">Department</label>
                            <select class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
                                <option>Engineering</option>
                                <option>Design</option>
                                <option>HR</option>
                                <option>Finance</option>
                                <option>Sales</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-900 mb-1 block">Payroll Month
                                <span class="text-danger">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" placeholder="dd/mm/yyyy" class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0 pr-10" data-provider="flatpickr" data-date-format="d M, Y">
                                <i class="ph ph-calendar-blank absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-900 mb-1 block">Payment Date</label>
                            <div class="relative">
                                <input type="text" placeholder="dd/mm/yyyy" class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0 pr-10" data-provider="flatpickr" data-date-format="d M, Y">
                                <i class="ph ph-calendar-blank absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-border-color rounded-md p-4">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Earnings</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-semibold text-gray-900 mb-1 block">Basic Salary
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-900 mb-1 block">HRA</label>
                            <input type="number" class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-900 mb-1 block">Conveyance</label>
                            <input type="number" class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-900 mb-1 block">Bonus</label>
                            <input type="number" class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-900 mb-1 block">Other Allowances</label>
                            <input type="number" class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-border-color rounded-md p-4">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Deductions</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-semibold text-gray-900 mb-1 block">Provident Fund (PF)</label>
                            <input type="number" class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-900 mb-1 block">Professional Tax</label>
                            <input type="number" class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-900 mb-1 block">TDS</label>
                            <input type="number" class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-900 mb-1 block">Other Deductions</label>
                            <input type="number" class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-12 lg:col-span-4 space-y-3">
                <div class="bg-white border border-border-color rounded-md p-4">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Net Salary</h2>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm text-default">
                            <span>Total Earnings</span><span>$0.00</span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-default"><span>Total Deductions
                            </span><span>$0.00</span>
                        </div>
                        <div class="flex items-center justify-between text-sm font-semibold text-title border-t border-border-color pt-2">
                            <span>Net Salary</span><span>$0.00</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-border-color rounded-md p-4">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Status</h2>
                    <select class="w-full px-3 py-2 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
                        <option>Paid</option>
                        <option>Pending</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <!-- End Content -->

@endsection
