<x-settings-form-page title="Add Region" subtitle="Settings → Branch → Regions" :back="route('regions.index')"
    :action="route('regions.store')" cardTitle="Region details" confirmTitle="Create this region?" confirmButton="Create">
    @include('regions._fields')
</x-settings-form-page>
