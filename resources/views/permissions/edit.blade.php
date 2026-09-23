<x-settings-form-page :title="'Edit Permission: '.$permission->name" subtitle="Settings → User Module → Permissions" :back="route('permissions.index')"
    :action="route('permissions.update', $permission)" method="PUT" cardTitle="Permission" confirmTitle="Rename this permission?"
    confirmNote="Code that checks the old name (@can, middleware) will stop matching. Rename only if the code is updated too.">
    @include('permissions._fields')
</x-settings-form-page>
