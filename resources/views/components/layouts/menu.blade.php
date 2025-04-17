@can('dashboard')
    <x-menu-item title="Dashboard" icon="fas.gauge" link="/dashboard" />
@endcan

@can('manage-dealers')
    <x-menu-item title="Dealers" icon="fas.shop" link="{{ route('dealers') }}" />
@endcan

@can('manage-products')
    <x-menu-item title="Products" icon="fas.box" link="{{ route('products') }}" />
@endcan

@can('manage-budget-period')
    <x-menu-item title="Budget Period" icon="fas.money-bill-wave" link="{{ route('budget-period') }}" />
@endcan

@can('manage-special-voucher')
    <x-menu-item title="Special Voucher" icon="fas.ticket" link="{{ route('special-voucher') }}" />
@endcan

@can('manage-users')
    <x-menu-item title="Users" icon="fas.users" link="{{ route('users') }}" />
@endcan

<x-menu-sub title="Settings" icon="fas.gear">
    @can('manage-roles')
        <x-menu-item title="Roles" icon="fas.user-tie" link="{{ route('roles') }}" />
    @endcan

    @can('manage-permissions')
        <x-menu-item title="Permissions" icon="fas.users-line" link="{{ route('permissions') }}" />
    @endcan
</x-menu-sub>

