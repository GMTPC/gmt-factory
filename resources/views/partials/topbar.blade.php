@if (Route::is(['pos']))
<!-- Header Start -->
<header class="px-4 py-3 flex items-center justify-between gap-4 flex-wrap border-b border-border-color">
    <div class="flex items-center gap-3">
        <a href="{{url('index')}}" class="flex items-center justify-center size-9 rounded-md border border-border-color bg-white text-default hover:bg-light focus:outline-hidden focus:bg-light">
            <i class="ph-duotone ph-arrow-left"></i>
        </a>
        <div class="relative w-64 search-input">
            <i class="ph ph-magnifying-glass absolute right-2.5 top-1/2 -translate-y-1/2 text-default text-sm"></i>
            <input type="text" class="w-full px-3 pe-8 py-2 h-9 text-[12px]! border border-border-color rounded-lg bg-white focus:outline-none focus:ring-0 placeholder:text-sm" placeholder="Search">
        </div>
    </div>
    <div class="flex items-center gap-2">
        <!-- Light/Dark Mode Button -->
        <div class="header-item">
            <button class="topbar-link items-center justify-center bg-white border border-border-color rounded-md light-dark-mode" type="button">
                <i class="ph-duotone ph-moon"></i>
            </button>
        </div>
        <!-- Language Dropdown -->
        <div class="header-item hs-dropdown [--placement:bottom-right] [--auto-close:inside] hidden lg:flex">
            <button type="button" class="topbar-link items-center justify-center bg-white border border-border-color rounded-md">
                <i class="ph-duotone ph-translate"></i>
            </button>
            <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-30 p-3 bg-white border border-border-color shadow rounded-md mt-[-8px]! z-1 space-y-1" role="menu" aria-orientation="vertical">
                <a href="#" class="flex items-center gap-2 px-2 py-[6px] rounded-md text-gray-900 hover:bg-light focus:outline-hidden focus:bg-white">
                    <img src="{{URL::asset('build/img/icons/us.svg')}}" class="size-4 rounded-full" alt="flag">ENG
                </a>
                <a href="#" class="flex items-center gap-2 px-2 py-[6px] rounded-md text-gray-900 hover:bg-light focus:outline-hidden focus:bg-white">
                    <img src="{{URL::asset('build/img/icons/arab.svg')}}" class="size-4 rounded-full" alt="flag">ARB
                </a>
                <a href="#" class="flex items-center gap-2 px-2 py-[6px] rounded-md text-gray-900 hover:bg-light focus:outline-hidden focus:bg-white">
                    <img src="{{URL::asset('build/img/icons/germany.svg')}}" class="size-4 rounded-full" alt="flag">GER
                </a>
            </div>
        </div>

        <!-- Notification Dropdown -->
        <div class="header-item hs-dropdown [--placement:bottom-right] [--auto-close:inside] hidden relative sm:flex" id="notification-dropdown">
            <button class="bg-white border border-border-color rounded-md size-8 flex items-center justify-center relative cursor-pointer">
                <i class="ph-duotone ph-bell"></i>
                <span class="absolute top-1 end-1 size-2 bg-danger-transparent rounded-full flex items-center justify-center">
                    <span class="size-1 bg-danger rounded-full"></span>
                </span>
            </button>
            <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-102 p-5 mt-[-8px]! bg-white border border-border-color shadow rounded-lg z-1 notification-dropdown" role="menu" aria-orientation="vertical">

                <div class="flex items-center justify-between border-b border-border-color pb-3">
                    <p class="text-base font-bold text-title">Notifications</p>
                    <div class="flex items-center gap-3">
                        <!-- The Toggle -->
                        <div class="flex items-center gap-1">
                            <label for="read" class="relative inline-block w-7 h-4 cursor-pointer">
                                <input type="checkbox" id="read" class="sr-only">
                                <span id="switch-track" class="absolute inset-0 bg-gray-200 rounded-full transition-colors duration-200"></span>
                                <span id="switch-thumb" class="absolute top-1/2 start-1 -translate-y-1/2 size-2.5 bg-white rounded-full transition-transform duration-200"></span>
                            </label>
                            <label for="read" class="cursor-pointer">Only Unread</label>
                        </div>
                        <button type="button" class="size-8 inline-flex justify-center items-center rounded-md border border-border-color bg-white text-gray-900 text-base hover:bg-danger hover:border-danger hover:text-white dark:hover:text-dark focus:outline-hidden focus:bg-danger cursor-pointer" aria-label="Close" id="close-notification">
                            <i class="icon-x"></i>
                        </button>
                    </div>
                </div>
                <div data-simplebar>
                    <div id="all-notifications">
                        <div class="py-5 space-y-3">

                            <!-- Item-->
                            <div class="notification-item relative flex items-center justify-between gap-2 p-2 rounded-md cursor-pointer hover:bg-light">
                                <div class="flex items-center">
                                    <div class="relative size-10 shrink-0 me-2">
                                        <img src="{{URL::asset('build/img/avatar/avatar-03.jpg')}}" class="rounded-full border border-border-color" alt="user">
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="font-semibold text-title inline-flex items-center mb-1">Project Deadline<i class="fa-solid fa-circle ms-1 text-[8px] text-danger"></i></p>
                                        <p class="text-[13px] mb-0">Project <span class="text-primary font-medium">‘ERP Upgrade’</span> is due in 3 days.</p>
                                    </div>
                                </div>
                                <p class="shrink-0">30 min</p>
                            </div>

                            <!-- Item-->
                            <div class="notification-item relative flex items-center justify-between gap-2 p-2 rounded-md cursor-pointer hover:bg-light">
                                <div class="flex items-center">
                                    <div class="relative size-10 font-semibold shrink-0 bg-secondary-transparent text-secondary rounded-full flex items-center justify-center me-2">
                                        AD
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="font-semibold text-title inline-flex items-center mb-1">New Order Placed<i class="fa-solid fa-circle ms-1 text-[8px] text-danger"></i></p>
                                        <p class="text-[13px] mb-0">Order <span class="text-primary font-medium">#SO-2345</span> has been placed by Adrian.</p>
                                    </div>
                                </div>
                                <p class="shrink-0">25 min</p>
                            </div>

                            <!-- Item-->
                            <div class="notification-item relative flex items-center justify-between gap-2 p-2 rounded-md cursor-pointer hover:bg-light">
                                <div class="flex items-center">
                                    <div class="relative size-10 shrink-0 me-2">
                                        <img src="{{URL::asset('build/img/avatar/avatar-07.jpg')}}" class="rounded-full border border-border-color" alt="user">
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="font-semibold text-title inline-flex items-center mb-1">Quotation Sent</p>
                                        <p class="text-[13px] mb-0">Quotation <span class="text-primary font-medium">#QT-451</span> sent to Hendy Jade.</p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <a href="#" class="inline-flex items-center btn-xsmall bg-dark border border-dark text-white  hover:bg-primary hover:border-primary hover:text-white">View Quotation</a>
                                            <a href="#" class="inline-flex items-center btn-xsmall bg-white border border-border-color text-gray-900  hover:bg-primary hover:border-primary hover:text-white dark:hover:text-white">Edit Quotation</a>
                                        </div>
                                    </div>
                                </div>
                                <p class="shrink-0">30 min</p>
                            </div>

                            <!-- Item-->
                            <div class="notification-item relative flex items-center justify-between gap-2 p-2 rounded-md cursor-pointer hover:bg-light">
                                <div class="flex items-center">
                                    <div class="relative size-10 font-semibold shrink-0 bg-warning text-white rounded-full flex items-center justify-center me-2">
                                        <i class="icon icon-pc-case"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="font-semibold text-title inline-flex items-center mb-1">Low Stock Alert</p>
                                        <p class="text-[13px] mb-0">Stock for Product A has dropped below the minimum threshold  <span class="text-primary font-medium">(15 units left).</span></p>
                                    </div>
                                </div>
                                <p class="shrink-0">30 min</p>
                            </div>

                            <!-- Item-->
                            <div class="notification-item relative flex items-center justify-between gap-2 p-2 rounded-md cursor-pointer hover:bg-light">
                                <div class="flex items-center">
                                    <div class="relative size-10 font-semibold shrink-0 bg-primary-transparent text-primary rounded-full flex items-center justify-center me-2">
                                        SJ
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="font-semibold text-title inline-flex items-center mb-1">Expense Added</p>
                                        <p class="text-[13px] mb-0">New expense entry <span class="text-primary font-medium">‘Office Supplies’</span> worth $250 has been recorded.</p>
                                    </div>
                                </div>
                                <p class="shrink-0">2 hrs ago</p>
                            </div>

                        </div>

                    </div>
                    <div class="hidden" id="unread-only">
                        <div class="py-5 space-y-3">

                            <!-- Item-->
                            <div class="notification-item relative flex items-center justify-between gap-2 p-2 rounded-md cursor-pointer hover:bg-light">
                                <div class="flex items-center">
                                    <div class="relative size-10 shrink-0 me-2">
                                        <img src="{{URL::asset('build/img/avatar/avatar-03.jpg')}}" class="rounded-full border border-border-color" alt="user">
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="font-semibold text-title inline-flex items-center mb-1">Project Deadline<i class="fa-solid fa-circle ms-1 text-[8px] text-danger"></i></p>
                                        <p class="text-[13px] mb-0">Project <span class="text-primary font-medium">‘ERP Upgrade’</span> is due in 3 days.</p>
                                    </div>
                                </div>
                                <p class="shrink-0">30 min</p>
                            </div>

                            <!-- Item-->
                            <div class="notification-item relative flex items-center justify-between gap-2 p-2 rounded-md cursor-pointer hover:bg-light">
                                <div class="flex items-center">
                                    <div class="relative size-10 font-semibold shrink-0 bg-secondary-transparent text-secondary rounded-full flex items-center justify-center me-2">
                                        AD
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="font-semibold text-title inline-flex items-center mb-1">New Order Placed<i class="fa-solid fa-circle ms-1 text-[8px] text-danger"></i></p>
                                        <p class="text-[13px] mb-0">Order <span class="text-primary font-medium">#SO-2345</span> has been placed by Adrian.</p>
                                    </div>
                                </div>
                                <p class="shrink-0">25 min</p>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="pt-3 border-t border-border-color flex items-center justify-between">
                    <a href="#" class="text-default fw-medium">Mark all as read</a>
                    <a href="#" class="text-primary font-medium hover:text-primary-hover">
                       View all Notifications
                    </a>
                </div>
            </div>
        </div>

        <!-- User Dropdown -->
        <div class="profile-dropdown hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative">
            <a href="#" class="hs-dropdown-toggle relative flex items-center gap-2 justify-center rounded-full lg:ms-2" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                <span class="hidden sm:block text-end">
                    <span class="block text-sm font-semibold text-title leading-tight">{{ auth()->user()->username }}</span>
                    <span class="block text-[11px] text-default leading-tight">{{ auth()->user()->department }}@if (auth()->user()->position) · {{ auth()->user()->position }}@endif</span>
                </span>
                <div class="size-8 rounded-full overflow-hidden border border-border-color">
                    <img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="size-full object-cover" alt="user-image">
                </div>
            </a>
            <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 p-5 bg-white border border-border-color shadow rounded-md mt-2 z-1 divide-y divide-border-color" role="menu" aria-orientation="vertical">
                <div class="flex items-center pb-3">
                    <div class="size-10 rounded-full relative me-2">
                        <img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="rounded-full border border-border-color" alt="user-image">
                    </div>
                    <div>
                        <p class="font-semibold text-title mb-1">{{ auth()->user()->username }}</p>
                        <p class="text-[13px] mb-0">Department: {{ auth()->user()->department }}</p>
                        @if (auth()->user()->position)<p class="text-[13px] mb-0">Position: {{ auth()->user()->position }}</p>@endif
                    </div>
                </div>
                <div class="py-3 space-y-1">
                    <a href="{{url('profile')}}" class="flex items-center px-2 py-[6px] rounded-md text-gray-900 hover:bg-light focus:outline-hidden focus:bg-white">
                        <i class="icon icon-user-round text-base me-2"></i>View Profile
                    </a>
                    <a href="{{url('edit-profile')}}" class="flex items-center px-2 py-[6px] rounded-md text-gray-900 hover:bg-light focus:outline-hidden focus:bg-white">
                        <i class="icon icon-user-pen text-base me-2"></i>Edit Profile
                    </a>
                    <a href="{{url('general-settings')}}" class="flex items-center px-2 py-[6px] rounded-md text-gray-900 hover:bg-light focus:outline-hidden focus:bg-white">
                        <i class="icon icon-cog text-base me-2"></i>Settings
                    </a>
                    <a href="{{url('tickets')}}" class="flex items-center px-2 py-[6px] rounded-md text-gray-900 hover:bg-light focus:outline-hidden focus:bg-white">
                        <i class="icon icon-align-start-horizontal text-base me-2"></i>Support Tickets
                    </a>
                </div>
                <div class="pt-3">
                    <a href="{{url('lock-screen')}}" class="flex items-center px-2 py-[6px] rounded-md text-gray-900 hover:bg-light focus:outline-hidden focus:bg-white">
                        <i class="icon icon-user-cog text-base me-2"></i>Lock Screen
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-start flex items-center px-2 py-[6px] rounded-md text-danger hover:bg-light focus:outline-hidden focus:bg-white">
                            <i class="icon icon-log-out text-base me-2"></i>Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- Header End -->
@else
<!-- Topbar Start -->
<header class="navbar-header flex items-center max-lg:w-full">
    <div class="topbar-menu flex items-center justify-between w-full gap-2">
        <div class="flex items-center gap-3">

            <!-- Sidebar Mobile Button -->
            <a id="mobile_btn" class="mobile-btn lg:hidden" href="#sidebar" aria-label="menu">
                <i class="icon icon-menu"></i>
            </a>

            <!-- Logo -->
            <a href="{{url('index')}}" class="logo">

                <!-- Logo Normal -->
                <span class="logo-light">
                    <span class="logo-lg"><img src="{{URL::asset('build/img/logo.svg')}}" alt="logo"></span>
                    <span class="logo-sm"><img src="{{URL::asset('build/img/logo-small.svg')}}" alt="small logo"></span>
                </span>

                <!-- Logo Dark -->
                <span class="logo-dark">
                    <span class="logo-lg"><img src="{{URL::asset('build/img/logo-white.svg')}}" alt="dark logo"></span>
                </span>
            </a>

            <button class="sidenav-toggle-btn topbar-link shrink-0 size-9 text-[20px] items-center justify-center rounded-full" id="toggle_btn2">
                <i class="ph-duotone ph-arrow-left"></i>
            </button>

        </div>

        <div class="flex items-center gap-2 lg:w-full">

            <!-- ป้ายบอกไลน์ปัจจุบัน — เติมโดย resources/js/layouts/AppLayout.vue (Vue Teleport) -->
            <div id="topbar-line-slot" class="flex min-w-0 items-center me-auto"></div>

            <!-- Light/Dark Mode Button -->
            <div class="header-item">
                <button class="topbar-link items-center justify-center light-dark-mode" type="button">
                    <i class="ph-duotone ph-moon"></i>
                </button>
            </div>

            <!-- Notification Dropdown -->
            <div class="header-item hs-dropdown [--placement:bottom-right] [--auto-close:inside] hidden relative sm:flex" id="notification-dropdown">
                <button class="bg-light border border-border-color rounded-md size-8 flex items-center justify-center relative cursor-pointer">
                    <i class="ph-duotone ph-bell"></i>
                    <span class="absolute top-1 end-1 size-2 bg-danger-transparent rounded-full flex items-center justify-center">
                        <span class="size-1 bg-danger rounded-full"></span>
                    </span>
                </button>
                <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-102 p-5 mt-[-8px]! bg-white border border-border-color shadow rounded-lg z-1 notification-dropdown" role="menu" aria-orientation="vertical">

                    <div class="flex items-center justify-between border-b border-border-color pb-3">
                        <p class="text-base font-bold text-title">Notifications</p>
                        <div class="flex items-center gap-3">
                            <!-- The Toggle -->
                            <div class="flex items-center gap-1">
                                <label for="read" class="relative inline-block w-7 h-4 cursor-pointer">
                                    <input type="checkbox" id="read" class="sr-only">
                                    <span id="switch-track" class="absolute inset-0 bg-gray-200 rounded-full transition-colors duration-200"></span>
                                    <span id="switch-thumb" class="absolute top-1/2 start-1 -translate-y-1/2 size-2.5 bg-white rounded-full transition-transform duration-200"></span>
                                </label>
                                <label for="read" class="cursor-pointer">Only Unread</label>
                            </div>
                            <button type="button" class="size-8 inline-flex justify-center items-center rounded-md border border-border-color bg-white text-gray-900 text-base hover:bg-danger hover:border-danger hover:text-white dark:hover:text-dark focus:outline-hidden focus:bg-danger cursor-pointer" aria-label="Close" id="close-notification">
                                <i class="icon-x"></i>
                            </button>
                        </div>
                    </div>
                    <div data-simplebar>
                        <div id="all-notifications">
                            <div class="py-5 space-y-3">

                                <!-- Item-->
                                <div class="notification-item relative flex items-center justify-between gap-2 p-2 rounded-md cursor-pointer hover:bg-light">
                                    <div class="flex items-center">
                                        <div class="relative size-10 shrink-0 me-2">
                                            <img src="{{URL::asset('build/img/avatar/avatar-03.jpg')}}" class="rounded-full border border-border-color" alt="user">
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="font-semibold text-title inline-flex items-center mb-1">Project Deadline<i class="fa-solid fa-circle ms-1 text-[8px] text-danger"></i></p>
                                            <p class="text-[13px] mb-0">Project <span class="text-primary font-medium">‘ERP Upgrade’</span> is due in 3 days.</p>
                                        </div>
                                    </div>
                                    <p class="shrink-0">30 min</p>
                                </div>

                                <!-- Item-->
                                <div class="notification-item relative flex items-center justify-between gap-2 p-2 rounded-md cursor-pointer hover:bg-light">
                                    <div class="flex items-center">
                                        <div class="relative size-10 font-semibold shrink-0 bg-secondary-transparent text-secondary rounded-full flex items-center justify-center me-2">
                                            AD
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="font-semibold text-title inline-flex items-center mb-1">New Order Placed<i class="fa-solid fa-circle ms-1 text-[8px] text-danger"></i></p>
                                            <p class="text-[13px] mb-0">Order <span class="text-primary font-medium">#SO-2345</span> has been placed by Adrian.</p>
                                        </div>
                                    </div>
                                    <p class="shrink-0">25 min</p>
                                </div>

                                <!-- Item-->
                                <div class="notification-item relative flex items-center justify-between gap-2 p-2 rounded-md cursor-pointer hover:bg-light">
                                    <div class="flex items-center">
                                        <div class="relative size-10 shrink-0 me-2">
                                            <img src="{{URL::asset('build/img/avatar/avatar-07.jpg')}}" class="rounded-full border border-border-color" alt="user">
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="font-semibold text-title inline-flex items-center mb-1">Quotation Sent</p>
                                            <p class="text-[13px] mb-0">Quotation <span class="text-primary font-medium">#QT-451</span> sent to Hendy Jade.</p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <a href="#" class="inline-flex items-center btn-xsmall bg-dark border border-dark text-white  hover:bg-primary hover:border-primary hover:text-white">View Quotation</a>
                                                <a href="#" class="inline-flex items-center btn-xsmall bg-white border border-border-color text-gray-900  hover:bg-primary hover:border-primary hover:text-white dark:hover:text-white">Edit Quotation</a>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="shrink-0">30 min</p>
                                </div>

                                <!-- Item-->
                                <div class="notification-item relative flex items-center justify-between gap-2 p-2 rounded-md cursor-pointer hover:bg-light">
                                    <div class="flex items-center">
                                        <div class="relative size-10 font-semibold shrink-0 bg-warning text-white rounded-full flex items-center justify-center me-2">
                                            <i class="icon icon-pc-case"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="font-semibold text-title inline-flex items-center mb-1">Low Stock Alert</p>
                                            <p class="text-[13px] mb-0">Stock for Product A has dropped below the minimum threshold  <span class="text-primary font-medium">(15 units left).</span></p>
                                        </div>
                                    </div>
                                    <p class="shrink-0">30 min</p>
                                </div>

                                <!-- Item-->
                                <div class="notification-item relative flex items-center justify-between gap-2 p-2 rounded-md cursor-pointer hover:bg-light">
                                    <div class="flex items-center">
                                        <div class="relative size-10 font-semibold shrink-0 bg-primary-transparent text-primary rounded-full flex items-center justify-center me-2">
                                            SJ
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="font-semibold text-title inline-flex items-center mb-1">Expense Added</p>
                                            <p class="text-[13px] mb-0">New expense entry <span class="text-primary font-medium">‘Office Supplies’</span> worth $250 has been recorded.</p>
                                        </div>
                                    </div>
                                    <p class="shrink-0">2 hrs ago</p>
                                </div>

                            </div>

                        </div>
                        <div class="hidden" id="unread-only">
                            <div class="py-5 space-y-3">

                                <!-- Item-->
                                <div class="notification-item relative flex items-center justify-between gap-2 p-2 rounded-md cursor-pointer hover:bg-light">
                                    <div class="flex items-center">
                                        <div class="relative size-10 shrink-0 me-2">
                                            <img src="{{URL::asset('build/img/avatar/avatar-03.jpg')}}" class="rounded-full border border-border-color" alt="user">
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="font-semibold text-title inline-flex items-center mb-1">Project Deadline<i class="fa-solid fa-circle ms-1 text-[8px] text-danger"></i></p>
                                            <p class="text-[13px] mb-0">Project <span class="text-primary font-medium">‘ERP Upgrade’</span> is due in 3 days.</p>
                                        </div>
                                    </div>
                                    <p class="shrink-0">30 min</p>
                                </div>

                                <!-- Item-->
                                <div class="notification-item relative flex items-center justify-between gap-2 p-2 rounded-md cursor-pointer hover:bg-light">
                                    <div class="flex items-center">
                                        <div class="relative size-10 font-semibold shrink-0 bg-secondary-transparent text-secondary rounded-full flex items-center justify-center me-2">
                                            AD
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="font-semibold text-title inline-flex items-center mb-1">New Order Placed<i class="fa-solid fa-circle ms-1 text-[8px] text-danger"></i></p>
                                            <p class="text-[13px] mb-0">Order <span class="text-primary font-medium">#SO-2345</span> has been placed by Adrian.</p>
                                        </div>
                                    </div>
                                    <p class="shrink-0">25 min</p>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="pt-3 border-t border-border-color flex items-center justify-between">
                        <a href="#" class="text-default fw-medium">Mark all as read</a>
                        <a href="#" class="text-primary font-medium hover:text-primary-hover">
                        View all Notifications
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mail / Inbox -->
            <div class="header-item hidden sm:flex">
                <a href="{{url('email')}}" class="topbar-link items-center justify-center relative">
                    <i class="ph-duotone ph-envelope"></i>
                    <span class="absolute top-1 end-1 size-2 bg-success-transparent rounded-full flex items-center justify-center">
                        <span class="size-1 bg-success rounded-full"></span>
                    </span>
                </a>
            </div>

            <!-- User Dropdown -->
            <div class="profile-dropdown hs-dropdown [--placement:bottom-right] [--auto-close:inside] relative">
                <a href="#" class="hs-dropdown-toggle relative flex items-center gap-2 justify-center rounded-full lg:ms-2" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                    <span class="hidden sm:block text-end">
                        <span class="block text-sm font-semibold text-title leading-tight">{{ auth()->user()->username }}</span>
                        <span class="block text-[11px] text-default leading-tight">{{ auth()->user()->department }}@if (auth()->user()->position) · {{ auth()->user()->position }}@endif</span>
                    </span>
                    <div class="size-8 rounded-full overflow-hidden border border-border-color">
                        <img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="size-full object-cover" alt="user-image">
                    </div>
                </a>
                <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 p-5 bg-white border border-border-color shadow rounded-md mt-2 z-1 divide-y divide-border-color" role="menu" aria-orientation="vertical">
                    <div class="flex items-center pb-3">
                        <div class="size-10 rounded-full relative me-2">
                            <img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="rounded-full border border-border-color" alt="user-image">
                        </div>
                        <div>
                            <p class="font-semibold text-title mb-1">{{ auth()->user()->username }}</p>
                            <p class="text-[13px] mb-0">Department: {{ auth()->user()->department }}</p>
                            @if (auth()->user()->position)<p class="text-[13px] mb-0">Position: {{ auth()->user()->position }}</p>@endif
                        </div>
                    </div>
                    <div class="py-3 space-y-1">
                        <a href="{{url('profile')}}" class="flex items-center px-2 py-[6px] rounded-md text-gray-900 hover:bg-light focus:outline-hidden focus:bg-white">
                            <i class="icon icon-user-round text-base me-2"></i>View Profile
                        </a>
                        <a href="{{url('edit-profile')}}" class="flex items-center px-2 py-[6px] rounded-md text-gray-900 hover:bg-light focus:outline-hidden focus:bg-white">
                            <i class="icon icon-user-pen text-base me-2"></i>Edit Profile
                        </a>
                        <a href="{{url('general-settings')}}" class="flex items-center px-2 py-[6px] rounded-md text-gray-900 hover:bg-light focus:outline-hidden focus:bg-white">
                            <i class="icon icon-cog text-base me-2"></i>Settings
                        </a>
                        <a href="{{url('tickets')}}" class="flex items-center px-2 py-[6px] rounded-md text-gray-900 hover:bg-light focus:outline-hidden focus:bg-white">
                            <i class="icon icon-align-start-horizontal text-base me-2"></i>Support Tickets
                        </a>
                    </div>
                    <div class="pt-3">
                        <a href="{{url('lock-screen')}}" class="flex items-center px-2 py-[6px] rounded-md text-gray-900 hover:bg-light focus:outline-hidden focus:bg-white">
                            <i class="icon icon-user-cog text-base me-2"></i>Lock Screen
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-start flex items-center px-2 py-[6px] rounded-md text-danger hover:bg-light focus:outline-hidden focus:bg-white">
                                <i class="icon icon-log-out text-base me-2"></i>Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</header>
<!-- Topbar End -->
@endif

<!-- Search Start -->
<div id="search_modal" class="hs-overlay hs-overlay-open:opacity-100 hs-overlay-open:duration-500 hidden size-full fixed top-0 start-0 z-80 opacity-0 overflow-x-hidden transition-all overflow-y-auto pointer-events-none" role="dialog" tabindex="-1">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-[630px] w-full mx-auto">
            <div class="flex flex-col bg-white border p-6 border-border-color rounded-lg pointer-events-auto m-5">
                <div class="flex justify-between items-center mb-5">
                    <p class="text-lg font-bold text-title">Search</p>
                    <button type="button" class="size-8 inline-flex justify-center items-center rounded-md border border-border-color bg-white text-gray-900 text-base hover:bg-danger hover:border-danger hover:text-white dark:hover:text-dark focus:outline-hidden focus:bg-danger cursor-pointer" aria-label="Close" data-hs-overlay="#search_modal">
                        <i class="icon-x"></i>
                    </button>
                </div>
                <div class="relative mb-5">
                    <input type="text" class="w-full ps-3 pe-12 py-1 h-8 bg-white border border-border-color text-title rounded-md focus:border-(--topbar-input-border) placeholder:(-topbar-input-placeholder) focus:ring-0 shadow" placeholder="Search Keywords" autocomplete="off">
                    <button type="button" class="cursor-pointer w-8 p-1 text-xs gap-1  flex items-center justify-center absolute end-1 top-1/2 -translate-y-1/2">
                        <i id="shortcutIcon" class="icon-search"></i>
                    </button>
                </div>
                <div class="mb-5">
                    <p class="text-title font-semibold mb-2">I’m Looking For</p>
                    <div class="flex items-center flex-wrap gap-3">
                        <a href="{{url('employees')}}" class="py-1 px-2 bg-light-transparent border border-border-color rounded-md text-xs font-medium text-title hover:bg-primary hover:border-primary hover:text-white">Employees</a>
                        <a href="{{url('deals')}}" class="py-1 px-2 bg-light-transparent border border-border-color rounded-md text-xs font-medium text-title hover:bg-primary hover:border-primary hover:text-white">Deals</a>
                        <a href="{{url('pos')}}" class="py-1 px-2 bg-light-transparent border border-border-color rounded-md text-xs font-medium text-title hover:bg-primary hover:border-primary hover:text-white">POS</a>
                        <a href="{{url('expenses')}}" class="py-1 px-2 bg-light-transparent border border-border-color rounded-md text-xs font-medium text-title hover:bg-primary hover:border-primary hover:text-white">Finance</a>
                        <a href="{{url('cash-sales')}}" class="py-1 px-2 bg-light-transparent border border-border-color rounded-md text-xs font-medium text-title hover:bg-primary hover:border-primary hover:text-white">Cash Sales</a>
                    </div>
                </div>
                <p class="text-title font-semibold mb-2">Quick Actions</p>
                <div class="space-y-1 mb-5">
                    <a href="{{url('general-settings')}}" class="flex items-center gap-2 rounded-md text-title py-[5px] px-[10px] hover:bg-light hover:text-title group"><i class="icon icon-cog text-base text-default group-hover:text-title"></i>Settings</a>
                    <a href="{{url('add-product')}}" class="flex items-center gap-2 rounded-md text-title py-[5px] px-[10px] hover:bg-light hover:text-title group"><i class="icon icon-circle-plus text-base text-default group-hover:text-title"></i>Create Products</a>
                    <a href="{{url('deals')}}" class="flex items-center gap-2 rounded-md text-title py-[5px] px-[10px] hover:bg-light hover:text-title group"><i class="icon icon-circle-plus text-base text-default group-hover:text-title"></i>Create Deals</a>
                    <a href="{{url('expenses')}}" class="flex items-center gap-2 rounded-md text-title py-[5px] px-[10px] hover:bg-light hover:text-title group"><i class="icon icon-circle-plus text-base text-default group-hover:text-title"></i>Create Expense</a>
                </div>
                <p class="text-title font-semibold mb-2">Search</p>
                <div class="space-y-1">
                    <a href="#" class="flex items-center gap-2 rounded-md text-title py-[5px] px-[10px] hover:bg-light hover:text-title"><i class="icon icon-clock-10 text-base text-default"></i>Anderson</a>
                    <a href="#" class="flex items-center gap-2 rounded-md text-title py-[5px] px-[10px] hover:bg-light hover:text-title"><i class="icon icon-clock-10 text-base text-default"></i>Oneplus 11G</a>
                    <a href="#" class="flex items-center gap-2 rounded-md text-title py-[5px] px-[10px] hover:bg-light hover:text-title"><i class="icon icon-clock-10 text-base text-default"></i>Oneplus 11G</a>
                    <a href="#" class="flex items-center gap-2 rounded-md text-title py-[5px] px-[10px] hover:bg-light hover:text-title"><i class="icon icon-clock-10 text-base text-default"></i>#INV45879</a>
                    <a href="#" class="flex items-center gap-2 rounded-md text-title py-[5px] px-[10px] hover:bg-light hover:text-title"><i class="icon icon-clock-10 text-base text-default"></i>Knowledge Base</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Search End -->
