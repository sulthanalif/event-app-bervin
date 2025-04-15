<x-menu-item title="Dashboard" icon="fas.gauge" link="/dashboard" />
<x-menu-item title="Dealers" icon="fas.shop" link="{{ route('dealers') }}" />
<x-menu-item title="Products" icon="fas.box" link="{{ route('products') }}" />

<x-menu-item title="Users" icon="fas.users" link="{{ route('users') }}" />
<x-menu-sub title="Settings" icon="fas.gear">
    <x-menu-item title="Roles" icon="fas.user-tie" link="{{ route('roles') }}" />
    <x-menu-item title="Permissions" icon="fas.users-line" link="{{ route('permissions') }}" />
</x-menu-sub>
