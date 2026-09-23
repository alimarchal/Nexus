<x-settings-form-page title="Add Manager" subtitle="Settings → User Module → Managers" :back="route('managers.index')"
    :action="route('managers.store')" cardTitle="Division manager" confirmTitle="Create this manager?" confirmButton="Create">
    @include('managers._fields')
</x-settings-form-page>
