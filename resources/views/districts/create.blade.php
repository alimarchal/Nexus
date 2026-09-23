<x-settings-form-page title="Add District" subtitle="Settings → Branch → Districts" :back="route('districts.index')"
    :action="route('districts.store')" cardTitle="District details" cardSubtitle="Every district belongs to one region."
    confirmTitle="Create this district?" confirmButton="Create">
    @include('districts._fields')
</x-settings-form-page>
