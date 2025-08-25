<aside id="layout-menu" class="layout-menu menu-vertical menu">
    <div class="app-brand demo">
        <a href="index.html" class="app-brand-link">
            <span class="app-brand-logo demo">
                <span class="text-primary">
                    <svg width="32" height="22" viewBox="0 0 32 22" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                            fill="currentColor" />
                        <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                            d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z" fill="#161616" />
                        <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                            d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z" fill="#161616" />
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                            fill="currentColor" />
                    </svg>
                </span>
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-3">Edara Tech</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="icon-base  menu-toggle-icon ti ti-percentage-0 d-xl-block"></i>
            <i class="icon-base ti tabler-x d-block d-xl-none"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base fa-solid fa-database"></i>
                <div>{{ __('Main Data') }}</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="{{ route('currencies.index') }}" class="menu-link">
                        <div>{{ __('Currency') }}</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('banks.index') }}" class="menu-link">
                        <div>{{ __('Banks') }}</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('cash-vaults.index') }}" class="menu-link">
                        <div>{{ __('Cash Vaults') }}</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('accounts.index') }}" class="menu-link">
                        <div>{{ __('Accounts Tree') }}</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('cost-centers.index') }}" class="menu-link">
                        <div>{{ __('Cost Centers') }}</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base fa-solid fa-gear"></i>
                <div>{{ __('Settings') }}</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="{{ route('accounting-settings.index') }}" class="menu-link">
                        <div>{{ __('Accounts Settings') }}</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('settings.index') }}" class="menu-link">
                        <div>{{ __('System Settings') }}</div>
                    </a>
                </li>
            </ul>
        </li>
    </ul>

</aside>

<div class="menu-mobile-toggler d-xl-none rounded-1">
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
        <i class="ti tabler-menu icon-base"></i>
        <i class="ti tabler-chevron-right icon-base"></i>
    </a>
</div>
