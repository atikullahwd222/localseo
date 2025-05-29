<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="/" class="app-brand-link">
            <span class="app-brand-logo demo">
                <x-application-logo width="200" />
            </span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('sites') ? 'active' : '' }}">
            <a href="{{ route('sites') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-globe"></i>
                <div data-i18n="Analytics">Sites</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('site.settings') ? 'active' : '' }}">
            <a href="{{ route('site.settings') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Analytics">Site Settings</div>
            </a>
        </li>

        <!-- Admin Area -->
        @if(auth()->user()->isAdmin() || auth()->user()->isEditor())
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Administration</span>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
                <div data-i18n="Admin Area">Admin Area</div>
            </a>
            <ul class="menu-sub">
                @if(auth()->user()->isAdmin() || auth()->user()->isEditor())
                <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-user"></i>
                        <div data-i18n="User Management">User Management</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.users.pending') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.pending') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-user-check"></i>
                        <div data-i18n="Pending Approvals">Pending Approvals</div>
                    </a>
                </li>
                @endif

                @if(auth()->user()->isAdmin())
                <li class="menu-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.roles.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-user-pin"></i>
                        <div data-i18n="Role Management">Role Management</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.permissions.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-key"></i>
                        <div data-i18n="Permission Management">Permission Management</div>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif

        <!-- Account Settings -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-user-circle"></i>
                <div data-i18n="Account Settings">Account Settings</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="{{ route('profile.edit') }}" class="menu-link">
                        <div data-i18n="Account">Account</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('settings') }}" class="menu-link">
                        <div data-i18n="Settings">Settings</div>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</aside>
