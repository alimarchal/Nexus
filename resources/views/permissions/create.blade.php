<x-settings-form-page title="Add Permission" subtitle="Settings → User Module → Permissions" :back="route('permissions.index')"
    :action="route('permissions.store')" cardTitle="Permission" confirmTitle="Create this permission?" confirmButton="Create">
    @include('permissions._fields')
</x-settings-form-page>
