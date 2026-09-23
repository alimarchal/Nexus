<x-settings-form-page :title="'Edit Role: '.$role->name" subtitle="Settings → User Module → Roles" :back="route('roles.index')" maxWidth="max-w-7xl"
    :action="route('roles.update', $role)" method="PUT" cardTitle="Role & permissions"
    cardSubtitle="Changes apply immediately to every user who has this role."
    confirmTitle="Save changes to this role?" confirmNote="Every user with this role gets or loses these permissions at once.">
    @include('roles._form')
</x-settings-form-page>
