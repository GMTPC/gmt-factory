<!-- Sidenav Menu Start -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="flex items-center">
            <a href="{{ url('cut-boards') }}" class="logo logo-normal">
                <img src="{{ asset('Login_v1/images/img-02.png') }}" class="max-h-10 max-w-40 object-contain" alt="Gypman Tech Logo">
            </a>
            <a href="{{ url('cut-boards') }}" class="logo-small">
                <img src="{{ asset('Login_v1/images/img-02.png') }}" class="max-h-10 max-w-10 object-contain" alt="Gypman Tech Logo">
            </a>
            <a href="{{ url('cut-boards') }}" class="dark-logo">
                <img src="{{ asset('Login_v1/images/img-02.png') }}" class="max-h-10 max-w-40 object-contain" alt="Gypman Tech Logo">
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
            <ul role="menu" aria-label="เมนู Cut Board">
                @php($isMasterMenuActive = request()->routeIs('products') || request()->routeIs('master-product-specs.*') || request()->routeIs('master-product-groups.*') || request()->routeIs('master-brands.*') || request()->routeIs('master-edges.*') || request()->routeIs('master-faces.*') || request()->routeIs('master-sheet-types.*') || request()->routeIs('master-sizes.*') || request()->routeIs('master-thicknesses.*'))
                <li class="submenu {{ $isMasterMenuActive ? 'open' : '' }}">
                    <a href="{{ route('products') }}" data-navigate-submenu="true" class="{{ $isMasterMenuActive ? 'active subdrop' : '' }}" aria-expanded="{{ $isMasterMenuActive ? 'true' : 'false' }}">
                        <i class="ph-duotone ph-database"></i>
                        <span>Master</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul style="{{ $isMasterMenuActive ? 'display: block;' : '' }}">
                        <li><a href="{{ route('products') }}" class="{{ request()->routeIs('products') ? 'active' : '' }}">Products</a></li>
                        <li><a href="{{ route('master-product-specs.index') }}" class="{{ request()->routeIs('master-product-specs.*') ? 'active' : '' }}">Product Specifications</a></li>
                        <li><a href="{{ route('master-product-groups.index') }}" class="{{ request()->routeIs('master-product-groups.*') ? 'active' : '' }}">Product Groups</a></li>
                        @foreach ([
                            'master-brands' => 'Brands',
                            'master-edges' => 'Edges',
                            'master-faces' => 'Faces',
                            'master-sheet-types' => 'Sheet Types',
                            'master-sizes' => 'Sizes',
                            'master-thicknesses' => 'Thicknesses',
                        ] as $catalogRoute => $catalogLabel)
                            <li><a href="{{ route($catalogRoute.'.index') }}" class="{{ request()->routeIs($catalogRoute.'.*') ? 'active' : '' }}">{{ $catalogLabel }}</a></li>
                        @endforeach
                    </ul>
                </li>
                <li class="menu-title" aria-disabled="true"><span>Inventory</span></li>
                <li>
                    <a href="{{ url('forklift-receive') }}" class="{{ request()->is('forklift-receive') ? 'active' : '' }}">
                        <i class="ph-duotone ph-truck-trailer"></i>
                        <span>Forklift Receive</span>
                    </a>
                </li>
                @php($gradeCLine = request()->route('line') ?? 'L1')
                <li>
                    <a href="{{ url('cut-boards/lines/'.$gradeCLine.'/grade-c') }}" class="{{ request()->routeIs('cut-board.lines.grade-c*') ? 'active' : '' }}">
                        <i class="ph-duotone ph-scissors"></i>
                        <span>คัดแผ่น C</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('bx-print') }}" class="{{ request()->is('bx-print*') ? 'active' : '' }}">
                        <i class="ph-duotone ph-printer"></i>
                        <span>BX Print</span>
                    </a>
                </li>
                @php($isFinishingActive = request()->routeIs('finishing.*'))
                <li class="menu-title mt-3" aria-disabled="true"><span>Finishing Production</span></li>
                <li>
                    <a href="{{ route('finishing.stations.index') }}" class="{{ request()->routeIs('finishing.stations.*', 'finishing.station.record') ? 'active' : '' }}">
                        <i class="ph-duotone ph-factory"></i>
                        <span>สถานีผลิต Finishing</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('finishing.dashboard') }}" class="{{ request()->routeIs('finishing.dashboard') ? 'active' : '' }}">
                        <i class="ph-duotone ph-chart-line-up"></i>
                        <span>Dashboard Finishing</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('finishing.reports') }}" class="{{ request()->routeIs('finishing.reports') ? 'active' : '' }}">
                        <i class="ph-duotone ph-file-text"></i>
                        <span>รายงาน Finishing</span>
                    </a>
                </li>
                @php($activeCutBoardLine = request()->routeIs('cut-board.lines.*') ? request()->route('line') : null)
                @php($activeCutBoardPanel = request()->route('section') ?? (request()->routeIs('cut-board.lines.history') ? 'history' : null))
                @php($cutBoardLineColors = ['L1' => '#059669', 'L2' => '#d97706', 'L3' => '#0369a1', 'L4' => '#7c3aed'])
                <li class="menu-title mt-3" aria-disabled="true"><span>QC คัดบอร์ดตามไลน์</span></li>
                @foreach (['L1' => 'ไลน์ 1', 'L2' => 'ไลน์ 2', 'L3' => 'ไลน์ 3', 'L4' => 'ไลน์ 4'] as $productionLine => $productionLineLabel)
                    @php($isCutBoardLineActive = $activeCutBoardLine === $productionLine)
                    <li class="submenu cb-line-menu {{ $isCutBoardLineActive ? 'open' : '' }}" data-line="{{ $productionLine }}" style="--cb-line-color: {{ $cutBoardLineColors[$productionLine] }};">
                        <a href="{{ url('cut-boards/lines/'.$productionLine) }}" data-navigate-submenu="true" class="{{ $isCutBoardLineActive ? 'active subdrop' : '' }}" aria-expanded="{{ $isCutBoardLineActive ? 'true' : 'false' }}" title="QC คัดบอร์ด {{ $productionLineLabel }}">
                            {{-- เลขไลน์ในกรอบสี แทนไอคอนเดียวกันทุกไลน์ เพื่อให้แยกไลน์ได้แม้ตอนย่อเมนู --}}
                            <i class="cb-line-num" aria-hidden="true">{{ substr($productionLine, 1) }}</i>
                            <span>QC คัดบอร์ด {{ $productionLineLabel }}</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul style="{{ $isCutBoardLineActive ? 'display: block;' : '' }}">
                            <li><a href="{{ url('cut-boards/lines/'.$productionLine) }}" class="{{ $isCutBoardLineActive && !$activeCutBoardPanel && request()->routeIs('cut-board.lines.show') ? 'active' : '' }}">ภาพรวมไลน์</a></li>
                            <li><a href="{{ url('cut-boards/lines/'.$productionLine.'/grade-c') }}" class="{{ $isCutBoardLineActive && request()->routeIs('cut-board.lines.grade-c*') ? 'active' : '' }}" @if ($isCutBoardLineActive && request()->routeIs('cut-board.lines.grade-c*')) aria-current="page" @endif>คัดแผ่น C</a></li>
                            @foreach (['history' => 'ประวัติงานที่ผ่านมา', 'employees' => 'รายชื่อพนักงาน', 'groups' => 'จัดกลุ่มพนักงาน', 'daily-summary' => 'สรุปยอดรายวัน'] as $panel => $panelLabel)
                                @php($isPanelActive = $isCutBoardLineActive && $activeCutBoardPanel === $panel)
                                <li>
                                    <a href="{{ url('cut-boards/lines/'.$productionLine.'/'.$panel) }}" class="{{ $isPanelActive ? 'active' : '' }}" @if ($isPanelActive) aria-current="page" @endif>{{ $panelLabel }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
                <li class="menu-title mt-3" aria-disabled="true"><span>ติดแท็กออนไลน์</span></li>
                @foreach (['L1' => 'ไลน์ 1', 'L2' => 'ไลน์ 2', 'L3' => 'ไลน์ 3', 'L4' => 'ไลน์ 4'] as $tagLine => $tagLineLabel)
                    @php($isOnlineTagLineActive = request()->routeIs('end-line-qc.*') && request()->route('line') === $tagLine)
                    <li>
                        <a href="{{ url('end-line-qc/'.$tagLine) }}" class="{{ $isOnlineTagLineActive ? 'active' : '' }}" @if ($isOnlineTagLineActive) aria-current="page" @endif style="--cb-line-color: {{ $cutBoardLineColors[$tagLine] }};">
                            <i class="cb-line-num" aria-hidden="true">{{ substr($tagLine, 1) }}</i>
                            <span>{{ $tagLineLabel }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</aside>
<!-- Sidenav Menu End -->
