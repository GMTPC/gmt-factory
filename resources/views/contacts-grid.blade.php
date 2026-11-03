@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">

        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 lg:mb-6">
            <h1 class="text-gray-900 text-xl font-bold mb-0">Contacts</h1>
            <div class="flex items-center gap-2">
                <div class="inline-flex bg-white border border-border-color rounded-md p-1">
                    <a href="{{url('contacts')}}" class="btn-sm bg-white text-gray-900 hover:bg-light inline-flex items-center gap-1 cursor-pointer">
                       <i class="ph-duotone ph-rows"></i>
                    </a>
                    <a href="{{url('contacts-grid')}}" class="btn-sm bg-dark text-white inline-flex items-center gap-1 cursor-pointer">
                        <i class="ph-duotone ph-grid-four"></i>
                    </a>
                </div>
                <button type="button" class="btn-sm bg-dark text-white border border-dark inline-flex items-center gap-2 hover:bg-primary-hover cursor-pointer" data-hs-overlay="#add-contacts-offcanvas">
                    <i class="ph ph-plus"></i> Add Contact
                </button>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex items-center gap-3 mb-3">
                    <img src="{{URL::asset('build/img/avatar/avatar-03.jpg')}}" class="size-12 rounded-full" alt="avatar">
                    <div class="flex-1">
                        <a href="{{url('contact-details')}}" class="text-sm font-semibold text-title hover:text-primary">Alexander Kenn</a>
                        <p class="text-sm text-default mb-0">CEO at TechCorp Inc</p>
                    </div>
                </div>
                <div class="text-sm text-default space-y-1 pt-3 border-t border-border-color">
                    <div class="flex items-center gap-1"><i class="ph ph-envelope"></i> alex@example.com</div>
                    <div class="flex items-center gap-1"><i class="ph ph-phone"></i> +1 202 555 0173</div>
                    <div class="flex items-center gap-1"><i class="ph ph-map-pin"></i> USA</div>
                </div>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex items-center gap-3 mb-3">
                    <img src="{{URL::asset('build/img/avatar/avatar-04.jpg')}}" class="size-12 rounded-full" alt="avatar">
                    <div class="flex-1">
                        <a href="{{url('contact-details')}}" class="text-sm font-semibold text-title hover:text-primary">Gabriella White</a>
                        <p class="text-sm text-default mb-0">CTO at InnoSoft</p>
                    </div>
                </div>
                <div class="text-sm text-default space-y-1 pt-3 border-t border-border-color">
                    <div class="flex items-center gap-1"><i class="ph ph-envelope"></i> gabby@example.com</div>
                    <div class="flex items-center gap-1"><i class="ph ph-phone"></i> +91 98765 43210</div>
                    <div class="flex items-center gap-1"><i class="ph ph-map-pin"></i> India</div>
                </div>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex items-center gap-3 mb-3">
                    <img src="{{URL::asset('build/img/avatar/avatar-05.jpg')}}" class="size-12 rounded-full" alt="avatar">
                    <div class="flex-1">
                        <a href="{{url('contact-details')}}" class="text-sm font-semibold text-title hover:text-primary">Christopher Rey</a>
                        <p class="text-sm text-default mb-0">Sales Director</p>
                    </div>
                </div>
                <div class="text-sm text-default space-y-1 pt-3 border-t border-border-color">
                    <div class="flex items-center gap-1"><i class="ph ph-envelope"></i> chris@example.com</div>
                    <div class="flex items-center gap-1"><i class="ph ph-phone"></i> +1 416 555 8294</div>
                    <div class="flex items-center gap-1"><i class="ph ph-map-pin"></i> Canada</div>
                </div>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex items-center gap-3 mb-3">
                    <img src="{{URL::asset('build/img/avatar/avatar-06.jpg')}}" class="size-12 rounded-full" alt="avatar">
                    <div class="flex-1">
                        <a href="{{url('contact-details')}}" class="text-sm font-semibold text-title hover:text-primary">Penelope Ton</a>
                        <p class="text-sm text-default mb-0">CFO at FinanceHub</p>
                    </div>
                </div>
                <div class="text-sm text-default space-y-1 pt-3 border-t border-border-color">
                    <div class="flex items-center gap-1"><i class="ph ph-envelope"></i> pton@example.com</div>
                    <div class="flex items-center gap-1"><i class="ph ph-phone"></i> +61 412 345 678</div>
                    <div class="flex items-center gap-1"><i class="ph ph-map-pin"></i> Australia</div>
                </div>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex items-center gap-3 mb-3">
                    <img src="{{URL::asset('build/img/avatar/avatar-07.jpg')}}" class="size-12 rounded-full" alt="avatar">
                    <div class="flex-1">
                        <a href="{{url('contact-details')}}" class="text-sm font-semibold text-title hover:text-primary">Daniel Foster</a>
                        <p class="text-sm text-default mb-0">Marketing Lead</p>
                    </div>
                </div>
                <div class="text-sm text-default space-y-1 pt-3 border-t border-border-color">
                    <div class="flex items-center gap-1"><i class="ph ph-envelope"></i> dan@example.com</div>
                    <div class="flex items-center gap-1"><i class="ph ph-phone"></i> +49 1512 3456789</div>
                    <div class="flex items-center gap-1"><i class="ph ph-map-pin"></i> Germany</div>
                </div>
            </div>
            <div class="bg-white border border-border-color rounded-md p-4">
                <div class="flex items-center gap-3 mb-3"><img src="{{URL::asset('build/img/avatar/avatar-08.jpg')}}" class="size-12 rounded-full" alt="avatar">
                    <div class="flex-1">
                        <a href="{{url('contact-details')}}" class="text-sm font-semibold text-title hover:text-primary">Anastasia Leton</a>
                        <p class="text-sm text-default mb-0">Project Manager</p>
                    </div>
                </div>
                <div class="text-sm text-default space-y-1 pt-3 border-t border-border-color">
                    <div class="flex items-center gap-1"><i class="ph ph-envelope"></i> ana@example.com</div>
                    <div class="flex items-center gap-1"><i class="ph ph-phone"></i> +81 90 1234 5678</div>
                    <div class="flex items-center gap-1"><i class="ph ph-map-pin"></i> Japan</div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Content -->

@endsection
