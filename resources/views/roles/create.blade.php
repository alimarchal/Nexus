<x-settings-form-page title="Add Role" subtitle="Settings → User Module → Roles" :back="route('roles.index')"
    :action="route('roles.store')" cardTitle="Role & permissions" cardSubtitle="A role is a named bundle of permissions (Spatie). Give users roles, and extra permissions only when needed."
    confirmTitle="Create this role?" confirmButton="Create">
    @include('roles._form')
</x-settings-form-page>
