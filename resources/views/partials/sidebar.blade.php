<!-- Sidenav Menu Start -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="flex items-center">
            <a href="{{ url('user-management') }}" class="logo logo-normal">
                <img src="{{ URL::asset('build/img/logo.svg') }}" alt="Logo">
            </a>
            <a href="{{ url('user-management') }}" class="logo-small">
                <img src="{{ URL::asset('build/img/logo-small.svg') }}" alt="Logo">
            </a>
            <a href="{{ url('user-management') }}" class="dark-logo">
                <img src="{{ URL::asset('build/img/logo-white.svg') }}" alt="Logo">
            </a>
        </div>

        <button class="sidenav-toggle-btn btn border-0 p-0 active" id="toggle_btn" aria-label="toggle">
            <i class="icon icon-panel-right-open align-middle"></i>
        </button>

        <button type="button" class="sidebar-close" aria-label="close">
            <i class="icon icon-x align-middle"></i>
        </button>
    </div>

    <div class="sidebar-inner" data-simplebar>
        <div id="sidebar-menu" class="sidebar-menu">
            <ul role="menu" aria-label="Main navigation menu">
                <li class="menu-title" aria-disabled="true"><span>Main</span></li>
                <li>
                    <a href="{{ url('user-management') }}" class="{{ request()->is('user-management') ? 'active' : '' }}">
                        <i class="ph-duotone ph-user"></i>
                        <span>User</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>
<!-- Sidenav Menu End -->
